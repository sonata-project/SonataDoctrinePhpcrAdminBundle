Form field definition
=====================

Short Object Placeholder
------------------------

When using Many-to-One or One-to-One relations with Sonata Type fields, a short
object description is used to represent the target object. If no object is selected,
a 'No selection' placeholder will be used. If you want to customize this placeholder,
you can use the corresponding option in the form field definition::

    namespace Sonata\NewsBundle\Admin;

    use Sonata\AdminBundle\Admin\AbstractAdmin;
    use Sonata\AdminBundle\Form\FormMapper;

    final class PostAdmin extends AbstractAdmin
    {
        protected function configureFormFields(FormMapper $formMapper)
        {
            $formMapper
                ->add('enabled', null, ['required' => false])
                ->add('author', 'sonata_type_model_list', [], [
                    'placeholder' => 'No author selected',
                ]);
        }
    }

This placeholder is translated using the SonataAdminBundle catalogue.
