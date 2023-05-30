<?php

namespace App\Controller\Admin;

use App\Entity\UserCredentials;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class UserCredentialsCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return UserCredentials::class;
    }

    /*
    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id'),
            TextField::new('title'),
            TextEditorField::new('description'),
        ];
    }
    */
}
