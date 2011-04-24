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

namespace Mandango;

/**
 * UnitOfWorkInterface.
 *
 * @author Pablo Díez <pablodip@gmail.com>
 *
 * @api
 */
interface UnitOfWorkInterface
{
    /**
     * Persist a document.
     *
     * @param Mandango\Document\Document|array $documents A document or an array of documents.
     *
     * @api
     */
    function persist($documents);

    /**
     * Remove a document.
     *
     * @param \Mandango\Document\Document|array $documents A document or an array of documents.
     *
     * @api
     */
    function remove($documents);

    /**
     * Commit pending persist and remove operations.
     *
     * @api
     */
    function commit();

    /**
     * Clear the pending operations
     *
     * @api
     */
    function clear();
}
