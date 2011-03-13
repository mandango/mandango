<?php

/*
 * Copyright 2010 Pablo Díez <pablodip@gmail.com>
 *
 * This file is part of Mandango.
 *
 * Mandango is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Mandango is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with Mandango. If not, see <http://www.gnu.org/licenses/>.
 */

namespace Mandango\Tests;

use Mandango\DataLoader;
use Mandango\Mandango;

class DataLoaderTest extends TestCase
{
    public function testConstructor()
    {
        $dataLoader = new DataLoader($this->mandango);
        $this->assertSame($this->mandango, $dataLoader->getMandango());
    }

    public function testSetGetMandango()
    {
        $dataLoader = new DataLoader($this->mandango);
        $dataLoader->setMandango($mandango = new Mandango($this->metadata, $this->queryCache));
        $this->assertSame($mandango, $dataLoader->getMandango());
    }

    public function testLoad()
    {
        $data = array(
            'Model\Article' => array(
                'article_1' => array(
                    'title'   => 'Article 1',
                    'content' => 'Contuent',
                    'author'  => 'sormes',
                    'categories' => array(
                        'category_2',
                        'category_3',
                    ),
                ),
                'article_2' => array(
                    'title' => 'My Article 2',
                ),
            ),
            'Model\Author' => array(
                'pablodip' => array(
                    'name' => 'PabloDip',
                ),
                'sormes' => array(
                    'name' => 'Francisco',
                ),
                'barbelith' => array(
                    'name' => 'Pedro',
                ),
            ),
            'Model\Category' => array(
                'category_1' => array(
                    'name' => 'Category1',
                ),
                'category_2' => array(
                    'name' => 'Category2',
                ),
                'category_3' => array(
                    'name' => 'Category3',
                ),
                'category_4' => array(
                    'name' => 'Category4',
                ),
            ),
        );

        $dataLoader = new DataLoader($this->mandango);
        $dataLoader->load($data);

        // articles
        $this->assertSame(2, \Model\Article::count());

        $article = \Model\Article::query(array('title' => 'Article 1'))->one();
        $this->assertNotNull($article);
        $this->assertSame('Contuent', $article->getContent());
        $this->assertSame('Francisco', $article->getAuthor()->getName());
        $this->assertSame(2, $article->getCategories()->count());

        $article = \Model\Article::query(array('title' => 'My Article 2'))->one();
        $this->assertNotNull($article);
        $this->assertNull($article->getAuthorId());

        // authors
        $this->assertSame(3, \Model\Author::count());

        $author = \Model\Author::query(array('name' => 'PabloDip'))->one();
        $this->assertNotNull($author);

        $author = \Model\Author::query(array('name' => 'Francisco'))->one();
        $this->assertNotNull($author);

        $author = \Model\Author::query(array('name' => 'Pedro'))->one();
        $this->assertNotNull($author);

        // categories
        $this->assertSame(4, \Model\Category::count());
    }

    public function testLoadPrune()
    {
        foreach ($this->mandango->getConnections() as $connection) {
            $connection->getMongoDB()->drop();
        }

        $data = array(
            'Model\Author' => array(
                'pablodip' => array(
                    'name' => 'Pablo',
                ),
            ),
        );

        $dataLoader = new DataLoader($this->mandango);

        $dataLoader->load($data);
        $this->assertSame(1, \Model\Author::repository()->count());

        $dataLoader->load($data);
        $this->assertSame(2, \Model\Author::repository()->count());

        $dataLoader->load($data, false);
        $this->assertSame(3, \Model\Author::repository()->count());

        $dataLoader->load($data, true);
        $this->assertSame(1, \Model\Author::repository()->count());
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testLoadMandangoUnitOfWorkHasPending()
    {
        $author = \Model\Author::create()->setName('Pablo');
        $this->mandango->persist($author);

        $dataLoader = new DataLoader($this->mandango);
        $dataLoader->load(array(
            'Model\Author' => array(
                'barbelith' => array(
                    'name' => 'Pedro',
                ),
            ),
        ));
    }
}
