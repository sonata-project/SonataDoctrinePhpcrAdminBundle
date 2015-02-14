<?php

/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\DoctrinePHPCRAdminBundle\Controller;

use Doctrine\ODM\PHPCR\Translation\Translation;
use PHPCR\AccessDeniedException;
use PHPCR\Util\PathHelper;
use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Admin\Pool;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Sonata\DoctrinePHPCRAdminBundle\Model\ModelManager;

class AutoCompleteController
{
    /**
     * @var \Sonata\AdminBundle\Admin\Pool
     */
    protected $pool;

    /**
     * @param \Sonata\AdminBundle\Admin\Pool $pool
     */
    public function __construct(Pool $pool)
    {
        $this->pool = $pool;
    }

    /**
     * @param Request $request
     * @return Response
     * @throws AccessDeniedException
     */
    public function autoCompleteAction(Request $request)
    {
        /** @var Admin $admin */
        $admin = $this->pool->getInstance($request->get('code'));
        $admin->setRequest($request);

        // check user permission
        if (false === $admin->isGranted('LIST')) {
            throw new AccessDeniedException();
        }

        // subject will be empty to avoid unnecessary database requests and keep auto-complete function fast
        $admin->setSubject($admin->getNewInstance());
        $fieldDescription = $this->retrieveFieldDescription($admin, $request->get('field'));
        $formAutocomplete = $admin->getForm()->get($fieldDescription->getName());

        if ($formAutocomplete->getConfig()->getAttribute('disabled')) {
            throw new AccessDeniedException('Autocomplete list can`t be retrieved because the form element is disabled or read_only.');
        }

        $class              = $formAutocomplete->getConfig()->getOption('class');
        $property           = $formAutocomplete->getConfig()->getAttribute('property');
        $minimumInputLength = $formAutocomplete->getConfig()->getAttribute('minimum_input_length');
        $itemsPerPage       = $formAutocomplete->getConfig()->getAttribute('items_per_page');
        $reqParamPageNumber = $formAutocomplete->getConfig()->getAttribute('req_param_name_page_number');
        $toStringCallback   = $formAutocomplete->getConfig()->getAttribute('to_string_callback');

        $searchText = $request->get('q');
        if (mb_strlen($searchText, 'UTF-8') < $minimumInputLength) {
            return new JsonResponse(array('status' => 'KO', 'message' => 'Too short search string.'), 403);
        }

        $page = $request->get($reqParamPageNumber);
        $offset = ($page - 1) * $itemsPerPage;

        /** @var ModelManager $modelManager */
        $modelManager = $formAutocomplete->getConfig()->getOption('model_manager');
        $dm = $modelManager->getDocumentManager();

        if ($class) {
            /** @var $qb \Doctrine\ODM\PHPCR\Query\Builder\QueryBuilder */
            $qb =  $dm->getRepository($class)->createQueryBuilder('a');
            $qb->where()->fullTextSearch("a.$property", '*' . $searchText . '*');
            $qb->setFirstResult($offset);
            //fetch one more to determine if there are more pages
            $qb->setMaxResults($itemsPerPage + 1);
            $query = $qb->getQuery();
            $results = $query->execute();
        } else {
            /** @var $qb \PHPCR\Util\QOM\QueryBuilder */
            $qb = $dm->createPhpcrQueryBuilder();
            // TODO: node type should probably be configurable
            $qb->from($qb->getQOMFactory()->selector('a', 'nt:unstructured'));
            $qb->where($qb->getQOMFactory()->fullTextSearch('a', $property, '*' . $searchText . '*'));
            // handle attribute translation
            $qb->orWhere($qb->getQOMFactory()->fullTextSearch('a', $dm->getTranslationStrategy('attribute')->getTranslatedPropertyName($request->getLocale(), $property), '*' . $searchText . '*'));
            $qb->setFirstResult($offset);
            //fetch one more to determine if there are more pages
            $qb->setMaxResults($itemsPerPage + 1);

            $results = $dm->getDocumentsByPhpcrQuery($qb->getQuery());
        }

        //did we max out x+1
        $more = (count($results) == $itemsPerPage + 1);
        $method = $request->get('_method_name');

        $items = array();
        foreach ($results as $path => $document) {
            // handle child translation
            if (strpos(PathHelper::getNodeName($path), Translation::LOCALE_NAMESPACE.':') === 0) {
                $document = $dm->find(null, PathHelper::getParentPath($path));
            }

            if (!method_exists($document, $method)) {
                continue;
            }

            $label = $document->{$method}();
            if ($toStringCallback !== null) {
                if (!is_callable($toStringCallback)) {
                    throw new \RuntimeException('Option "to_string_callback" does not contain callable function.');
                }

                $label = call_user_func($toStringCallback, $document, $property);
            }

            $items[] = array(
                'id'    => $admin->id($document),
                'label' => $label,
            );
        }

        return new JsonResponse(array(
            'status' => 'OK',
            'more'   => $more,
            'items'  => $items
        ));

    }

    /**
     * Retrieve the field description given by field name.
     *
     * @param AdminInterface $admin
     * @param string         $field
     *
     * @return \Symfony\Component\Form\FormInterface
     *
     * @throws \RuntimeException
     */
    private function retrieveFieldDescription(AdminInterface $admin, $field)
    {
        $admin->getFormFieldDescriptions();

        $fieldDescription = $admin->getFormFieldDescription($field);

        if (!$fieldDescription) {
            throw new \RuntimeException(sprintf('The field "%s" does not exist.', $field));
        }

        if ($fieldDescription->getType() !== 'sonata_type_model_autocomplete') {
            throw new \RuntimeException(sprintf('Unsupported form type "%s" for field "%s".', $fieldDescription->getType(), $field));
        }

        return $fieldDescription;
    }
}
