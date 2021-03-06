<?php

namespace Koriit\EventDispatcher\Test\UnitTests;

use DI\ContainerBuilder;
use Koriit\EventDispatcher\EventDispatcher;
use Koriit\EventDispatcher\Exceptions\InvalidPriority;

class EventDispatcherTest extends \PHPUnit_Framework_TestCase
{
    protected static $mockListener;

    /**
     * @var EventDispatcher
     */
    protected $dispatcher;

    public static function setUpBeforeClass()
    {
        self::$mockListener = function () {
        };
    }

    public function setUp()
    {
        $invoker = ContainerBuilder::buildDevContainer();
        $this->dispatcher = new EventDispatcher($invoker);
    }

    /**
     * @test
     */
    public function shouldAllowAddingListeners()
    {
        $eventName = 'mock';
        $this->dispatcher->addListener($eventName, self::$mockListener);

        $this->assertTrue($this->dispatcher->hasListeners());
        $this->assertTrue($this->dispatcher->hasListeners($eventName));

        $listneres = $this->dispatcher->getListeners($eventName);
        $this->assertFalse(empty($listneres));
        $this->assertEquals(self::$mockListener, $listneres[0][0]);

        $allListeners = $this->dispatcher->getAllListeners();
        $this->assertFalse(empty($allListeners));
        $this->assertEquals(self::$mockListener, $allListeners[$eventName][0][0]);
    }

    /**
     * @test
     * @depends shouldAllowAddingListeners
     */
    public function shouldAllowRemovingListeners()
    {
        $eventName = 'mock';
        $this->dispatcher->addListener($eventName, self::$mockListener);
        $this->dispatcher->removeListener($eventName, self::$mockListener);

        $this->assertFalse($this->dispatcher->hasListeners());
        $this->assertFalse($this->dispatcher->hasListeners($eventName));

        $listneres = $this->dispatcher->getListeners($eventName);
        $this->assertTrue(empty($listneres));

        $allListeners = $this->dispatcher->getAllListeners();
        $this->assertTrue(empty($allListeners));
    }

    /**
     * @test
     */
    public function shouldAllowRemovingListenersForNonexistentEvents()
    {
        $eventName = 'mock';
        $this->assertEmpty($this->dispatcher->getAllListeners());
        $this->dispatcher->removeListener($eventName, self::$mockListener);
    }

    /**
     * @test
     */
    public function shouldNotAllowNegativePriority()
    {
        $this->setExpectedException(InvalidPriority::class);

        $this->dispatcher->addListener('test', self::$mockListener, -1);
    }

    /**
     * @test
     */
    public function shouldAllowOnlyIntegerPriority()
    {
        $this->setExpectedException(InvalidPriority::class);

        $this->dispatcher->addListener('test', self::$mockListener, 'priority');
    }

    /**
     * @test
     */
    public function shouldNotAllowNegativePriorityWithBulk()
    {
        $this->setExpectedException(InvalidPriority::class);

        $listeners = [
            'mockEvent' => [
                -1 => [
                    function () {
                    },
                ],
            ],
        ];

        $this->dispatcher->addListeners($listeners);
    }

    /**
     * @test
     */
    public function shouldAllowOnlyIntegerPriorityWithBulk()
    {
        $this->setExpectedException(InvalidPriority::class);

        $listeners = [
            'mockEvent' => [
                'priority' => [
                    function () {
                    },
                ],
            ],
        ];

        $this->dispatcher->addListeners($listeners);
    }

    /**
     * @test
     * @dataProvider bulkListenersProvider
     *
     * @param array $manualListeners
     * @param array $bulkListeners
     * @param array $expected
     */
    public function shouldAllowAddingBulkListeners($manualListeners, $bulkListeners, $expected)
    {
        foreach ($manualListeners as $eventName => $byPriority) {
            foreach ($byPriority as $priority => $listeners) {
                foreach ($listeners as $listener) {
                    $this->dispatcher->addListener($eventName, $listener, $priority);
                }
            }
        }
        $this->dispatcher->addListeners($bulkListeners);

        $this->assertEquals($expected, $this->dispatcher->getAllListeners());
    }

    /**
     * Provides:
     * 1. Listeners to be added manually.
     * 2. Listeners to be added in bulk.
     * 3. How final listeners array should look like.
     *
     * @return array Test cases
     */
    public function bulkListenersProvider()
    {
        return [
          'onlyBulk' => [
              [],
              [
                'mockEvent' => [
                    0 => [
                        function () {
                        },
                    ],
                ],
              ],
              [
                'mockEvent' => [
                    0 => [
                        function () {
                        },
                    ],
                ],
              ],
          ],
          'onlyManual' => [
              [
                'mockEvent' => [
                    0 => [
                        function () {
                        },
                    ],
                ],
              ],
              [],
              [
                'mockEvent' => [
                    0 => [
                        function () {
                        },
                    ],
                ],
              ],
          ],
          'sameBulkAndManual' => [
              [
                'mockEvent' => [
                    0 => [
                        function () {
                        },
                    ],
                ],
              ],
              [
                'mockEvent' => [
                    0 => [
                        function () {
                        },
                    ],
                ],
              ],
              [
                'mockEvent' => [
                    0 => [
                        function () {
                        },
                        function () {
                        },
                    ],
                ],
              ],
          ],
          'sameBulkAndManualDiffPriority' => [
              [
                'mockEvent' => [
                    0 => [
                        function () {
                        },
                    ],
                ],
              ],
              [
                'mockEvent' => [
                    1 => [
                        function () {
                        },
                    ],
                ],
              ],
              [
                'mockEvent' => [
                    0 => [
                        function () {
                        },
                    ],
                    1 => [
                        function () {
                        },
                    ],
                ],
              ],
          ],
          'diffBulkAndManual' => [
              [
                'mockEvent2' => [
                    0 => [
                        function () {
                        },
                    ],
                ],
              ],
              [
                'mockEvent' => [
                    0 => [
                        function () {
                        },
                    ],
                ],
              ],
              [
                'mockEvent' => [
                    0 => [
                        function () {
                        },
                    ],
                ],
                'mockEvent2' => [
                    0 => [
                        function () {
                        },
                    ],
                ],
              ],
          ],
        ];
    }
}
