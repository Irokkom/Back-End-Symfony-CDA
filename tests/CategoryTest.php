<?php

namespace App\Tests\Unit;

use App\Entity\Category;
use PHPUnit\Framework\TestCase;

class CategoryTest extends TestCase
{
    public function testCategoryName(): void
    {
        // Création d'une instance de Category
        $category = new Category();
        
        // Test du setter et getter pour le nom
        $name = 'Technologie';
        $category->setName($name);
        
        // Vérification que la méthode getName retourne bien la valeur définie
        $this->assertEquals($name, $category->getName());
    }
}