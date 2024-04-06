<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Blog Pro for Magento 2
 */

namespace Amasty\Blog\Api;

/**
 * Interface VoteRepositoryInterface
 * @api
 */
interface VoteRepositoryInterface
{
    /**
     * @param \Amasty\Blog\Api\Data\VoteInterface $vote
     * @return \Amasty\Blog\Api\Data\VoteInterface
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function save(\Amasty\Blog\Api\Data\VoteInterface $vote);

    /**
     * @param int $voteId
     * @return \Amasty\Blog\Api\Data\VoteInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function get($voteId);

    /**
     * @param Data\VoteInterface $vote
     * @return mixed
     */
    public function delete(\Amasty\Blog\Api\Data\VoteInterface $vote);

    /**
     * @param int $voteId
     *
     * @return boolean
     */
    public function deleteById($voteId);
}
