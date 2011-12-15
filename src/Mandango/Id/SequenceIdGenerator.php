<?php

/*
 * This file is part of Mandango.
 *
 * (c) Pablo Díez <pablodip@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Mandango\Id;

use Mandango\Document\Document;

/**
 * Generates a sequence.
 *
 * @author Pablo Díez <pablodip@gmail.com>
 */
class SequenceIdGenerator extends BaseIdGenerator
{
    /**
     * {@inheritdoc}
     */
    public function getCode(array $options)
    {
        $increment = isset($options['increment']) ? $options['increment'] : 1;
        $start = isset($options['start']) ? $options['start'] : null;

        // increment
        if (!is_int($increment) || 0 === $increment) {
            throw new \InvalidArgumentException('The option "increment" must be an integer distinct of 0.');
        }

        // start
        if (null === $start) {
            $start = $increment > 0 ? 1 : -1;
        } elseif (!is_int($start) || 0 === $start) {
            throw new \InvalidArgumentException('The option "start" must be an integer distinct of 0.');
        }

        return <<<EOF
\$serverInfo = \$repository->getConnection()->getMongo()->selectDB('admin')->command(array('buildinfo' => true));
\$mongoVersion = \$serverInfo['version'];

\$commandResult = \$repository->getConnection()->getMongoDB()->command(array(
    'findandmodify' => 'mandango_sequence_id_generator',
    'query'         => array('_id' => \$repository->getCollectionName()),
    'update'        => array('\$inc' => array('sequence' => $increment)),
    'new'           => true,
));
if (
    (version_compare(\$mongoVersion, '2.0', '<') && \$commandResult['ok'])
    ||
    (version_compare(\$mongoVersion, '2.0', '>=') && null !== \$commandResult['value'])
) {
    %id% = \$commandResult['value']['sequence'];
} else {
    \$repository
        ->getConnection()
        ->getMongoDB()
        ->selectCollection('mandango_sequence_id_generator')
        ->insert(array('_id' => \$repository->getCollectionName(), 'sequence' => $start)
    );
    %id% = $start;
}
EOF;
    }

    /**
     * {@inheritdoc}
     */
    public function getToMongoCode()
    {
        return <<<EOF
%id% = (int) %id%;
EOF;
    }
}
