<?php
use FastD\Http\Bag\FileBag;

/**
 *
 * @author    jan huang <bboyjanhuang@gmail.com>
 * @copyright 2016
 *
 * @link      https://www.github.com/janhuang
 * @link      http://www.fast-d.cn/
 */
class FileBagTest extends PHPUnit_Framework_TestCase
{
    public function testFileBag()
    {
        $fileBag = new FileBag([
            'file' => [
                'name' => 'test.html',
                'type' => 'text/html',
                'tmp_name' => '/tmp/' . uniqid(),
                'error' => UPLOAD_ERR_OK,
                'size' => 123
            ]
        ]);

        $files = $fileBag->getFiles();

        $this->assertEquals([
            'file' => new \FastD\Http\Bag\File(),
        ], $files);

        $fileBag = new FileBag([
            'file' => [
                'name' => 'test.html',
                'type' => 'text/html',
                'tmp_name' => '/tmp/' . uniqid(),
                'error' => UPLOAD_ERR_OK,
                'size' => 123
            ],
            'name' => [
                'name' => 'test.html',
                'type' => 'text/html',
                'tmp_name' => '/tmp/' . uniqid(),
                'error' => UPLOAD_ERR_OK,
                'size' => 123
            ]
        ]);

        $this->assertEquals([
            'file' => new \FastD\Http\Bag\File(),
            'name' => new \FastD\Http\Bag\File(),
        ], $fileBag->getFiles());

        $fileBag = new FileBag([
            'files' => [
                'name' => [
                    'test.html',
                    'test2.html',
                ],
                'type' => [
                    'text/html',
                    'text/html',
                ],
                'tmp_name' => [
                    '/tmp/' . uniqid(),
                    '/tmp/' . uniqid()
                ],
                'error' => [
                    UPLOAD_ERR_OK,
                    UPLOAD_ERR_OK,
                ],
                'size' => [
                    123, 123
                ],

            ],
        ]);

        $this->assertEquals([
            'files' => [
                new \FastD\Http\Bag\File(),
                new \FastD\Http\Bag\File()
            ],
        ], $fileBag->getFiles());

        $fileBag = new FileBag([
            'files' => [
                'test' => [
                    'test2' => [
                        'name' => [
                            'test.html',
                            'test2.html',
                        ],
                        'type' => [
                            'text/html',
                            'text/html',
                        ],
                        'tmp_name' => [
                            '/tmp/' . uniqid(),
                            '/tmp/' . uniqid()
                        ],
                        'error' => [
                            UPLOAD_ERR_OK,
                            UPLOAD_ERR_OK,
                        ],
                        'size' => [
                            123, 123
                        ],
                    ]
                ]
            ],
        ]);

        $this->assertEquals($fileBag->getFiles(), [
            'files' => [
                'test' => [
                    'test2' => [
                        new \FastD\Http\Bag\File(),
                        new \FastD\Http\Bag\File(),
                    ]
                ]
            ]
        ]);

        $fileBag = new FileBag([
            'files' => [
                'test' => [
                    'name' => [
                        'test.html',
                        'test2.html',
                    ],
                    'type' => [
                        'text/html',
                        'text/html',
                    ],
                    'tmp_name' => [
                        '/tmp/' . uniqid(),
                        '/tmp/' . uniqid()
                    ],
                    'error' => [
                        UPLOAD_ERR_OK,
                        UPLOAD_ERR_OK,
                    ],
                    'size' => [
                        123, 123
                    ],
                ]
            ],
        ]);

        $this->assertEquals($fileBag->getFiles(), [
            'files' => [
                'test' => [
                    new \FastD\Http\Bag\File(),
                    new \FastD\Http\Bag\File(),
                ]
            ]
        ]);
    }
}
