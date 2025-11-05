<?php

declare(strict_types=1);

namespace App\Model;

/**
 * 博客标签模型
 */
class BlogTag extends Model
{
    /**
     * 表名
     * @var string
     */
    protected $table = 'blog_tags';

    /**
     * 主键
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * 可填充字段
     * @var array
     */
    protected $fillable = [
        'name',
        'slug',
        'description',
        'use_count',
        'created_at',
        'updated_at',
    ];

    /**
     * 隐藏字段
     * @var array
     */
    protected $hidden = [];

    /**
     * 时间戳字段
     * @var array
     */
    protected $casts = [
        'created_at' => 'timestamp',
        'updated_at' => 'timestamp',
        'use_count' => 'integer',
    ];

    /**
     * 获取标签关联的博客
     * @return \Hyperf\Model\Relations\BelongsToMany
     */
    public function blogs()
    {
        return $this->belongsToMany(
            Blog::class,
            'blog_tag_pivot',
            'tag_id',
            'blog_id'
        );
    }
}