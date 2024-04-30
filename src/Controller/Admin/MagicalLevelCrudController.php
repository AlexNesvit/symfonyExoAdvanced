<?php

namespace App\Controller\Admin;

use App\Entity\MagicalLevel;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class MagicalLevelCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return MagicalLevel::class;
    }

    
    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('name'),
            TextareaField::new('description'),
        ];
    }
    
}
