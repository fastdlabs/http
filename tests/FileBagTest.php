<?php
use FastD\Http\Bag\FileBag;

use FastD\Http\UploadedFile;

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
                'tmp_name' => '/tmp/b',
                'error' => UPLOAD_ERR_OK,
                'size' => 123
            ]
        ]);

        $files = $fileBag->getFiles();

        $this->assertEquals([
            'file' => new UploadedFile('test.html', 'text/html', '/tmp/b', 0, 123),
        ], $files);

        $fileBag = new FileBag([
            'file' => [
                'name' => 'test.html',
                'type' => 'text/html',
                'tmp_name' => '/tmp/a',
                'error' => UPLOAD_ERR_OK,
                'size' => 123
            ],
            'name' => [
                'name' => 'test.html',
                'type' => 'text/html',
                'tmp_name' => '/tmp/b',
                'error' => UPLOAD_ERR_OK,
                'size' => 123
            ]
        ]);

        $this->assertEquals([
            'file' => new UploadedFile('test.html', 'text/html', '/tmp/a', 0, 123),
            'name' => new UploadedFile('test.html', 'text/html', '/tmp/b', 0, 123),
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
                    '/tmp/a',
                    '/tmp/b'
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
                new UploadedFile('test.html', 'text/html', '/tmp/a', 0, 123),
                new UploadedFile('test2.html', 'text/html', '/tmp/b', 0, 123),
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
                            '/tmp/a',
                            '/tmp/b'
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
                        new UploadedFile('test.html', 'text/html', '/tmp/a', 0, 123),
                        new UploadedFile('test2.html', 'text/html', '/tmp/b', 0, 123),
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
                        '/tmp/a',
                        '/tmp/b'
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
                    new UploadedFile('test.html', 'text/html', '/tmp/a', 0, 123),
                    new UploadedFile('test2.html', 'text/html', '/tmp/b', 0, 123),
                ]
            ]
        ]);
    }

    public function testUpload()
    {

    }
}
