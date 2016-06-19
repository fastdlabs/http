<?php
/**
 * Created by PhpStorm.
 * User: janhuang
 * Date: 16/3/24
 * Time: 下午10:05
 * Github: https://www.github.com/janhuang
 * Coding: https://www.coding.net/janhuang
 * SegmentFault: http://segmentfault.com/u/janhuang
 * Blog: http://segmentfault.com/blog/janhuang
 * Gmail: bboyjanhuang@gmail.com
 * WebSite: http://www.janhuang.me
 */

namespace FastD\Http\Tests\Upload;

use FastD\Http\Attribute\FilesAttribute;

class UploaderTest extends \PHPUnit_Framework_TestCase
{
    protected $one = [];

    protected $multi = [];

    public function setUp()
    {
        $file = __DIR__ . '/tmp/test.jpg';

        $this->one = [
            'file' => [
                'name' => 'test.jpg',
                'type' => 'image/jpeg',
                'size' => filesize($file),
                'tmp_name' => $file,
                'error' => 0
            ],
            'test' => [
                'name' => 'test.jpg',
                'type' => 'image/jpeg',
                'size' => filesize($file),
                'tmp_name' => $file,
                'error' => 0
            ]
        ];
    }

    public function testUploadOne()
    {
        $filesBag = new FilesAttribute($this->one);

        $upload = new Uploader();

        $upload->setFiles($filesBag->all());

        $upload->uploadTo(__DIR__ . '/upload');

        $files = $upload->getUploadedFiles();

        $this->assertEquals(2, count($files));

        $this->assertEquals($files['test']->getAbsolutePath(), $files['file']->getAbsolutePath());
    }

    public function testUploadError()
    {
        
    }
}
