<?php

/*
 * This file is part of Mandango.
 *
 * (c) Pablo Díez <pablodip@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Mandango\Twig;

use Mandango\Type\Container as TypeContainer;

/**
 * The "mandango" extension for twig (used in the Core Mondator extension).
 *
 * @author Pablo Díez <pablodip@gmail.com>
 */
class Mandango extends \Twig_Extension
{
    public function getFilters()
    {
        return array(
            'ucfirst'    => new \Twig_Filter_Function('ucfirst'),
            'var_export' => new \Twig_Filter_Function('var_export'),
        );
    }

    public function getFunctions()
    {
        return array(
            'mandango_type_to_mongo' => new \Twig_Function_Method($this, 'mandangoTypeToMongo'),
            'mandango_type_to_php' => new \Twig_Function_Method($this, 'mandangoTypeToPHP'),
        );
    }

    public function mandangoTypeToMongo($type, $from, $to)
    {
        return strtr(TypeContainer::get($type)->toMongoInString(), array(
            '%from%' => $from,
            '%to%'   => $to,
        ));
    }

    public function mandangoTypeToPHP($type, $from, $to)
    {
        return strtr(TypeContainer::get($type)->toPHPInString(), array(
            '%from%' => $from,
            '%to%'   => $to,
        ));
    }

    public function getName()
    {
        return 'mandango';
    }
}
