<?php
/**
 * This file is part of the RedKite CMS Application and it is distributed
 * under the MIT License. To use this application you must leave
 * intact this copyright notice.
 *
 * Copyright (c) RedKite Labs <webmaster@redkite-labs.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * For extra documentation and help please visit http://www.redkite-labs.com
 *
 * @license    MIT License
 *
 */

namespace RedKiteLabs\RedKiteCms\RedKiteCmsBundle\Core\Repository\Propel;

use RedKiteLabs\RedKiteCms\RedKiteCmsBundle\Model\Block;
use RedKiteLabs\RedKiteCms\RedKiteCmsBundle\Model\BlockQuery;
use RedKiteLabs\RedKiteCms\RedKiteCmsBundle\Core\Repository\Repository\BlockRepositoryInterface;
use RedKiteLabs\RedKiteCms\RedKiteCmsBundle\Core\Exception\Content\General\InvalidArgumentTypeException;

/**
 *  Implements the BlockRepositoryInterface to work with Propel
 *
 *  @author RedKite Labs <webmaster@redkite-labs.com>
 */
class BlockRepositoryPropel extends Base\PropelRepository implements BlockRepositoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function getRepositoryObjectClassName()
    {
        return '\RedKiteLabs\RedKiteCms\RedKiteCmsBundle\Model\Block';
    }

    /**
     * {@inheritdoc}
     */
    public function setRepositoryObject($object = null)
    {
        if (null !== $object && !$object instanceof Block) {
            throw new InvalidArgumentTypeException('exception_only_propel_blocks_are_accepted');
        }

        return parent::setRepositoryObject($object);
    }

    /**
     * {@inheritdoc}
     */
    public function fromPK($id)
    {
        return BlockQuery::create()->findPk($id);
    }

    /**
     * {@inheritdoc}
     */
    public function retrieveContents($idLanguage, $idPage, $slotName = null, $toDelete = 0)
    {
        return BlockQuery::create()
                ->_if($idPage)
                    ->filterByPageId($idPage)
                ->_endif()
                ->_if($idLanguage)
                    ->filterByLanguageId($idLanguage)
                ->_endif()
                ->_if($slotName)
                    ->filterBySlotName($slotName)
                ->_endif()
                ->filterByToDelete($toDelete)
                ->orderBySlotName()
                ->orderByContentPosition()
                ->find();
    }

    /**
     * {@inheritdoc}
     */
    public function retrieveContentsBySlotName($slotName, $toDelete = 0)
    {
        return BlockQuery::create('a')
                ->where('a.SlotName like ?', $slotName)
                ->filterByToDelete($toDelete)
                ->orderBySlotName()
                ->find();
    }

    /**
     * {@inheritdoc}
     */
    public function fromLanguageId($languageId)
    {
        return BlockQuery::create()
                ->filterByLanguageId($languageId)
                ->filterByToDelete(0)
                ->find();
    }

    /**
     * {@inheritdoc}
     */
    public function fromPageId($pageId)
    {
        return BlockQuery::create()
                ->filterByPageId($pageId)
                ->filterByToDelete(0)
                ->find();
    }

    /**
     * {@inheritdoc}
     */
    public function fromContent($search)
    {
        return BlockQuery::create()
                ->filterByContent('%' . $search . '%')
                ->filterByToDelete(0)
                ->find();
    }

    /**
     * {@inheritdoc}
     */
    public function fromType($className, $operation = 'find')
    {
        return BlockQuery::create()
                ->filterByType($className)
                ->filterByToDelete(0)
                ->$operation();
    }

    /**
     * {@inheritdoc}
     */
    public function deleteBlocks($idLanguage, $idPage, $remove = false)
    {
        $blocks = BlockQuery::create()
                ->_if($idLanguage)
                    ->filterByLanguageId($idLanguage)
                ->_endif()
                ->_if($idPage)
                    ->filterByPageId($idPage)
                ->_endif();

        if ($remove) {
            $blocks->delete();
        } else {
            $blocks->update(array('ToDelete' => '1'));
        }
    }
}
