<?php

namespace Tests\Unit;

use App\Models\Post;
use PHPUnit\Framework\TestCase;

class PostTest extends TestCase
{
    public function test_post_title_is_converted_to_slug()
    {
        $post = new Post();

        $post->title = 'FPCI Goes to Campus 2026';

        $this->assertEquals(
            'fpci-goes-to-campus-2026',
            $post->slug
        );
    }
}