<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * Post Entity
 *
 * @property int $id
 * @property int $user_id
 * @property string $title
 * @property string $subtitle
 * @property string $content
 * @property string $imageURL
 * @property string $post_status
 * @property \Cake\I18n\FrozenTime $published_at
 *
 * @property \App\Model\Entity\User $user
 * @property \App\Model\Entity\Comment[] $comments
 */
class Post extends Entity
{
    /**
     * Fields that can be mass assigned using newEntity() or patchEntity().
     *
     * Note that when '*' is set to true, this allows all unspecified fields to
     * be mass assigned. For security purposes, it is advised to set '*' to false
     * (or remove it), and explicitly make individual fields accessible as needed.
     *
     * @var array
     */
    protected $_accessible = [
        'user_id' => true,
        'title' => true,
        'subtitle' => true,
        'content' => true,
        'imageURL' => true,
        'post_status' => true,
        'published_at' => true,
        'user' => true,
        'comments' => true,
    ];
}
