<?php

namespace Pumukit\SchemaBundle\Services;

use Pumukit\SchemaBundle\Document\MultimediaObject;
use Pumukit\SchemaBundle\Document\EmbeddedEvent;
use Doctrine\ODM\MongoDB\DocumentManager;

class EmbeddedEventSessionService
{
    private $dm;
    private $repo;
    private $collection;
    private $defaultPoster;
    const DEFAULT_COLOR = 'white';
    private $validColors = array(
        'aliceblue',
        'antiquewhite',
        'aqua',
        'aquamarine',
        'azure',
        'beige',
        'bisque',
        'black',
        'blanchedalmond',
        'blue',
        'blueviolet',
        'brown',
        'burlywood',
        'cadetblue',
        'chartreuse',
        'chocolate',
        'coral',
        'cornflowerblue',
        'cornsilk',
        'crimson',
        'cyan',
        'darkblue',
        'darkcyan',
        'darkgoldenrod',
        'darkgray',
        'darkgreen',
        'darkkhaki',
        'darkmagenta',
        'darkolivegreen',
        'darkorange',
        'darkorchid',
        'darkred',
        'darksalmon',
        'darkseagreen',
        'darkslateblue',
        'darkslategray',
        'darkturquoise',
        'darkviolet',
        'deeppink',
        'deepskyblue',
        'dimgray',
        'dodgerblue',
        'firebrick',
        'floralwhite',
        'forestgreen',
        'fuchsia',
        'gainsboro',
        'ghostwhite',
        'gold',
        'goldenrod',
        'gray',
        'green',
        'greenyellow',
        'honeydew',
        'hotpink',
        'indianred',
        'indigo',
        'ivory',
        'khaki',
        'lavender',
        'lavenderblush',
        'lawngreen',
        'lemonchiffon',
        'lightblue',
        'lightcoral',
        'lightcyan',
        'lightgoldenrodyellow',
        'lightgreen',
        'lightgrey',
        'lightpink',
        'lightsalmon',
        'lightseagreen',
        'lightskyblue',
        'lightslategray',
        'lightsteelblue',
        'lightyellow',
        'lime',
        'limegreen',
        'linen',
        'magenta',
        'maroon',
        'mediumaquamarine',
        'mediumblue',
        'mediumorchid',
        'mediumpurple',
        'mediumseagreen',
        'mediumslateblue',
        'mediumspringgreen',
        'mediumturquoise',
        'mediumvioletred',
        'midnightblue',
        'mintcream',
        'mistyrose',
        'moccasin',
        'navajowhite',
        'navy',
        'oldlace',
        'olive',
        'olivedrab',
        'orange',
        'orangered',
        'orchid',
        'palegoldenrod',
        'palegreen',
        'paleturquoise',
        'palevioletred',
        'papayawhip',
        'peachpuff',
        'peru',
        'pink',
        'plum',
        'powderblue',
        'purple',
        'red',
        'rosybrown',
        'royalblue',
        'saddlebrown',
        'salmon',
        'sandybrown',
        'seagreen',
        'seashell',
        'sienna',
        'silver',
        'skyblue',
        'slateblue',
        'slategray',
        'snow',
        'springgreen',
        'steelblue',
        'tan',
        'teal',
        'thistle',
        'tomato',
        'turquoise',
        'violet',
        'wheat',
        'white',
        'whitesmoke',
        'yellow',
        'yellowgreen',
    );

    /**
     * Constructor.
     */
    public function __construct(DocumentManager $documentManager, $defaultPoster)
    {
        $this->dm = $documentManager;
        $this->repo = $this->dm->getRepository('PumukitSchemaBundle:MultimediaObject');
        $this->collection = $this->dm->getDocumentCollection('PumukitSchemaBundle:MultimediaObject');
        $this->defaultPoster = $defaultPoster;
    }

    /**
     * Get default poster.
     *
     * @return string
     */
    public function getDefaultPoster()
    {
        return $this->defaultPoster;
    }

    /**
     * Find current events.
     */
    public function findEventsNow()
    {
        $now = new \MongoDate();
        $pipeline = $this->initPipeline();
        $pipeline[] = array(
            '$match' => array(
                'sessions.start' => array('$exists' => true),
                'sessionEnds' => array('$gte' => $now),
                'sessions.start' => array('$lte' => $now),
            ),
        );
        $pipeline[] = array(
            '$sort' => array(
                'sessions.start' => -1,
            ),
        );
        $this->endPipeline($pipeline);
        $pipeline[] = array('$limit' => 10);

        return $this->collection->aggregate($pipeline)->toArray();
    }

    /**
     * Find today events.
     */
    public function findEventsToday()
    {
        $todayStarts = strtotime(date('Y-m-d H:i:s', mktime(00, 00, 00, date('m'), date('d'), date('Y'))));
        $todayEnds = strtotime(date('Y-m-d H:i:s', mktime(23, 59, 59, date('m'), date('d'), date('Y'))));
        $pipeline = $this->initPipeline();
        $pipeline[] = array(
            '$match' => array('$and' => array(
                array('sessions.start' => array('$gte' => new \MongoDate($todayStarts))),
                array('sessions.start' => array('$lte' => new \MongoDate($todayEnds))),
            )),
        );
        $this->endPipeline($pipeline);
        $pipeline[] = array('$limit' => 20);

        return $this->collection->aggregate($pipeline)->toArray();
    }

    /**
     * Find next events.
     */
    public function findNextEvents()
    {
        $todayEnds = strtotime(date('Y-m-d H:i:s', mktime(23, 59, 59, date('m'), date('d'), date('Y'))));
        $pipeline = $this->initPipeline();
        $pipeline[] = array(
            '$match' => array(
                'sessions.start' => array('$exists' => true),
                'sessions.start' => array('$gte' => new \MongoDate($todayEnds)),
            ),
        );
        $pipeline[] = array(
            '$sort' => array(
                'sessions.start' => 1,
            ),
        );
        $this->endPipeline($pipeline);

        return $this->collection->aggregate($pipeline)->toArray();
    }

    /**
     * Get event poster.
     *
     * @param EmbeddedEvent $event
     *
     * @return string
     */
    public function getEventPoster(EmbeddedEvent $event)
    {
        $pics = $this->getMultimediaObjectPics($event->getId());

        return $this->getPoster($pics);
    }

    /**
     * Get event poster by event id.
     *
     * @param string $id
     *
     * @return string
     */
    public function getEventPosterByEventId($eventId)
    {
        $pics = $this->getMultimediaObjectPics($eventId);

        return $this->getPoster($pics);
    }

    /**
     * Get poster text color.
     *
     * @param EmbeddedEvent $event
     *
     * @return string
     */
    public function getPosterTextColor(EmbeddedEvent $event)
    {
        $properties = $this->getMultimediaObjectProperties($event->getId());
        if (isset($properties['postertextcolor'])) {
            return $properties['postertextcolor'];
        }

        return self::DEFAULT_COLOR;
    }

    /**
     * Validate HTML Color.
     *
     * @param string $color
     *
     * @return string
     */
    public function validateHtmlColor($color)
    {
        if (in_array(strtolower($color), $this->validColors) ||
        preg_match('/^#[a-f0-9]{3}$/i', $color) ||
        preg_match('/^#[a-f0-9]{6}$/i', $color)) {
            return $color;
        }
        if (preg_match('/^[a-f0-9]{6}$/i', $color) ||
        preg_match('/^[a-f0-9]{3}$/i', $color)) {
            return '#'.$color;
        }
        throw new \Exception('Invalid text color: must be a hexadecimal number or a color name.');
    }

    /**
     * Get current session date.
     *
     * @param EmbeddedEvent
     * @param bool
     *
     * @returns Date
     */
    public function getCurrentSessionDate(EmbeddedEvent $event, $start = true)
    {
        $now = new \DateTime('now');
        $date = new \DateTime('now');
        $sessions = $event->getEmbeddedEventSession();
        foreach ($sessions as $session) {
            if ($session->getStart() < $now && $session->getEnds() > $now) {
                $date = $start ? $session->getStart() : $session->getEnds();
            }
        }

        return $date;
    }

    /**
     * Get first session date.
     *
     * @param EmbeddedEvent
     * @param bool
     *
     * @returns Date
     */
    public function getFirstSessionDate(EmbeddedEvent $event, $start = true)
    {
        foreach ($event->getEmbeddedEventSession() as $session) {
            if ($start && $session->getStart()) {
                return $session->getStart();
            }
            if (!$start && $session->getEnds()) {
                return $session->getEnds();
            }
        }

        return $event->getDate();
    }

    /**
     * Get future session date.
     *
     * @param EmbeddedEvent
     * @param bool
     *
     * @returns Date
     */
    public function getFutureSessionDate($event, $start = true)
    {
        if (isset($event['embeddedEventSession'])) {
            $date = $event['date'];
            foreach ($event['embeddedEventSession'] as $session) {
                if ($start && isset($session['start'])) {
                    $date = $session['start'];

                    return $date->toDateTime();
                }
                if (!$start && isset($session['ends'])) {
                    $date = $session['ends'];

                    return $date->toDateTime();
                }
            }

            return $date->toDateTime();
        }

        return '';
    }

    /**
     * Get current session date.
     *
     * @param EmbeddedEvent
     * @param bool
     *
     * @returns Date
     */
    public function getShowEventSessionDate(EmbeddedEvent $event, $start = true)
    {
        $now = new \DateTime('now');
        $sessions = $event->getEmbeddedEventSession();
        foreach ($sessions as $session) {
            if ($session->getStart() < $now && $session->getEnds() > $now) {
                return $start ? $session->getStart() : $session->getEnds();
            } elseif ($session->getStart() > $now) {
                return $start ? $session->getStart() : $session->getEnds();
            } elseif ($session->getStart() < $now) {
                return $start ? $session->getStart() : $session->getEnds();
            }
        }

        return false;
    }

    /**
     * Find future events.
     *
     * @param $multimediaObjectId
     *
     * @return array
     */
    public function findFutureEvents($multimediaObjectId = null, $limit = 0)
    {
        $pipeline = $this->getFutureEventsPipeline($multimediaObjectId);
        $result = $this->collection->aggregate($pipeline)->toArray();
        $orderSession = array();
        foreach ($result as $key => $element) {
            foreach ($element['data'] as $eventData) {
                foreach ($eventData['event']['embeddedEventSession'] as $embeddedSession) {
                    $orderSession = $this->addElementWithSessionSec($orderSession, $element, $embeddedSession['start']->sec);
                    break;
                }
            }
        }
        ksort($orderSession);
        $output = array();
        foreach (array_values($orderSession) as $key => $session) {
            if ($limit !== 0 && $key >= $limit) {
                break;
            }
            $output[$key] = $session;
        }

        return $output;
    }

    /**
     * Count future events.
     *
     * @param $multimediaObjectId
     *
     * @return array
     */
    public function countFutureEvents($multimediaObjectId = null)
    {
        $pipeline = $this->getFutureEventsPipeline($multimediaObjectId);
        $result = $this->collection->aggregate($pipeline)->toArray();

        return count($result);
    }

    /**
     * Find all events.
     *
     * @return array
     */
    public function findAllEvents()
    {
        $pipeline[] = array(
            '$match' => array(
                'islive' => true,
                'embeddedEvent.display' => true,
                'embeddedEvent.embeddedEventSession' => array('$exists' => true),
            ),
        );
        $pipeline[] = array(
            '$project' => array(
                'multimediaObjectId' => '$_id',
                'event' => '$embeddedEvent',
                'sessions' => '$embeddedEvent.embeddedEventSession',
            ),
        );
        $pipeline[] = array('$unwind' => '$sessions');
        $pipeline[] = array(
            '$match' => array(
                'sessions.start' => array('$exists' => true),
            ),
        );
        $pipeline[] = array(
            '$project' => array(
                'multimediaObjectId' => '$multimediaObjectId',
                'event' => '$event',
                'sessions' => '$sessions',
                'session' => '$sessions',
            ),
        );
        $pipeline[] = array(
            '$group' => array(
                '_id' => '$multimediaObjectId',
                'data' => array(
                    '$addToSet' => array(
                        'event' => '$event',
                    ),
                ),
            ),
        );

        return $this->collection->aggregate($pipeline)->toArray();
    }

    /**
     * Init pipeline.
     *
     * @return array
     */
    private function initPipeline()
    {
        $pipeline = array();
        $pipeline[] = array(
            '$match' => array(
                'islive' => true,
                'embeddedEvent.display' => true,
                'embeddedEvent.embeddedEventSession' => array('$exists' => true),
            ),
        );
        $pipeline[] = array(
            '$project' => array(
                'multimediaObjectId' => '$_id',
                'event' => '$embeddedEvent',
                'sessions' => '$embeddedEvent.embeddedEventSession',
                'pics' => '$pics',
            ),
        );
        $pipeline[] = array('$unwind' => '$sessions');
        $pipeline[] = array(
            '$project' => array(
                'multimediaObjectId' => '$multimediaObjectId',
                'event' => '$event',
                'sessions' => '$sessions',
                'pics' => '$pics',
                'sessionEnds' => array(
                    '$add' => array(
                        '$sessions.start',
                        array(
                            '$multiply' => array(
                                '$sessions.duration',
                                1000,
                            ),
                        ),
                    ),
                ),
            ),
        );

        return $pipeline;
    }

    /**
     * End pipeline.
     *
     * @param array pipeline
     */
    private function endPipeline(&$pipeline)
    {
        $pipeline[] = array(
            '$group' => array(
                '_id' => '$multimediaObjectId',
                'data' => array(
                    '$first' => array(
                        'event' => '$event',
                        'session' => '$sessions',
                        'multimediaObjectId' => '$multimediaObjectId',
                        'pics' => '$pics',
                    ),
                ),
            ),
        );
        $pipeline[] = array(
            '$sort' => array(
                'data.session.start' => 1,
            ),
        );
    }

    /**
     * Get future events pipeline.
     *
     * @param string MultimediaObjectId
     *
     * @return array
     */
    private function getFutureEventsPipeline($multimediaObjectId)
    {
        if ($multimediaObjectId) {
            $pipeline[] = array(
                '$match' => array(
                    '_id' => new \MongoId($multimediaObjectId),
                    'islive' => true,
                    'embeddedEvent.embeddedEventSession' => array('$exists' => true),
                ),
            );
        } else {
            $pipeline[] = array(
                '$match' => array(
                    'islive' => true,
                    'embeddedEvent.display' => true,
                    'embeddedEvent.embeddedEventSession' => array('$exists' => true),
                ),
            );
        }
        $pipeline[] = array(
            '$project' => array(
                'multimediaObjectId' => '$_id',
                'event' => '$embeddedEvent',
                'sessions' => '$embeddedEvent.embeddedEventSession',
            ),
        );
        $pipeline[] = array('$unwind' => '$sessions');
        $pipeline[] = array(
            '$match' => array(
                'sessions.start' => array('$exists' => true),
                'sessions.start' => array('$gt' => new \MongoDate()),
            ),
        );
        $pipeline[] = array(
            '$project' => array(
                'multimediaObjectId' => '$multimediaObjectId',
                'event' => '$event',
                'sessions' => '$sessions',
                'session' => '$sessions',
            ),
        );
        $pipeline[] = array(
            '$group' => array(
                '_id' => '$multimediaObjectId',
                'data' => array(
                    '$addToSet' => array(
                        'event' => '$event',
                    ),
                ),
            ),
        );

        return $pipeline;
    }

    /**
     * Get multimedia object pics.
     *
     * @param string eventId
     *
     * @return array
     */
    private function getMultimediaObjectPics($eventId)
    {
        $pipeline = array();
        $pipeline[] = array(
            '$match' => array(
                'embeddedEvent._id' => new \MongoId($eventId),
            ),
        );
        $pipeline[] = array(
            '$project' => array(
                'multimediaObjectId' => '$_id',
                'pics' => '$pics',
            ),
        );
        $pipeline[] = array(
            '$group' => array(
                '_id' => '$multimediaObjectId',
                'data' => array(
                    '$first' => array(
                        'multimediaObjectId' => '$multimediaObjectId',
                        'pics' => '$pics',
                    ),
                ),
            ),
        );
        $data = $this->collection->aggregate($pipeline)->toArray();
        if (isset($data[0]['data']['pics'])) {
            return $data[0]['data']['pics'];
        }

        return array();
    }

    /**
     * Get poster.
     *
     * @param array
     *
     * @return string
     */
    private function getPoster($pics)
    {
        foreach ($pics as $pic) {
            if (isset($pic['tags'])) {
                if (in_array('poster', $pic['tags']) && isset($pic['url'])) {
                    return $pic['url'];
                }
            }
        }

        return $this->defaultPoster;
    }

    /**
     * Get multimedia object properties.
     *
     * @param string eventId
     *
     * @return array
     */
    private function getMultimediaObjectProperties($eventId)
    {
        $pipeline = array();
        $pipeline[] = array(
            '$match' => array(
                'embeddedEvent._id' => new \MongoId($eventId),
            ),
        );
        $pipeline[] = array(
            '$project' => array(
                'multimediaObjectId' => '$_id',
                'properties' => '$properties',
            ),
        );
        $pipeline[] = array(
            '$group' => array(
                '_id' => '$multimediaObjectId',
                'data' => array(
                    '$first' => array(
                        'multimediaObjectId' => '$multimediaObjectId',
                        'properties' => '$properties',
                    ),
                ),
            ),
        );
        $data = $this->collection->aggregate($pipeline)->toArray();
        if (isset($data[0]['data']['properties'])) {
            return $data[0]['data']['properties'];
        }

        return array();
    }

    /**
     * Find next live events.
     *
     * @param $multimediaObjectId
     *
     * @return array
     */
    public function findNextLiveEvents($multimediaObjectId = null, $limit = 0)
    {
        $pipeline = $this->getNextLiveEventsPipeline($multimediaObjectId);
        $result = $this->collection->aggregate($pipeline)->toArray();
        $orderSession = array();
        foreach ($result as $key => $element) {
            foreach ($element['data'] as $eventData) {
                foreach ($eventData['event']['embeddedEventSession'] as $embeddedSession) {
                    $orderSession = $this->addElementWithSessionSec($orderSession, $element, $embeddedSession['start']->sec);
                    break;
                }
            }
        }
        ksort($orderSession);
        $output = array();
        foreach (array_values($orderSession) as $key => $session) {
            if ($limit !== 0 && $key >= $limit) {
                break;
            }
            $output[$key] = $session;
        }

        return $output;
    }

    /**
     * Get next live events pipeline.
     *
     * @param string multimediaObjectId
     *
     * @return array
     */
    private function getNextLiveEventsPipeline($multimediaObjectId)
    {
        if ($multimediaObjectId) {
            $pipeline[] = array(
                '$match' => array(
                    '_id' => array('$nin' => array(new \MongoId($multimediaObjectId))),
                    'islive' => true,
                    'embeddedEvent.embeddedEventSession' => array('$exists' => true),
                ),
            );
        } else {
            $pipeline[] = array(
                '$match' => array(
                    'islive' => true,
                    'embeddedEvent.display' => true,
                    'embeddedEvent.embeddedEventSession' => array('$exists' => true),
                ),
            );
        }
        $pipeline[] = array(
            '$project' => array(
                'multimediaObjectId' => '$_id',
                'event' => '$embeddedEvent',
                'sessions' => '$embeddedEvent.embeddedEventSession',
            ),
        );
        $pipeline[] = array('$unwind' => '$sessions');
        $now = new \MongoDate();
        $today = new \MongoDate((new \DateTime('now'))->setTime(0, 0)->format('U'));
        $pipeline[] = array(
            '$match' => array(
                'sessions.start' => array('$exists' => true),
                'sessions.start' => array('$gte' => $today),
                'sessions.ends' => array('$exists' => true),
                'sessions.ends' => array('$gte' => $now),
            ),
        );
        $pipeline[] = array(
            '$project' => array(
                'multimediaObjectId' => '$multimediaObjectId',
                'event' => '$event',
                'sessions' => '$sessions',
                'session' => '$sessions',
            ),
        );
        $pipeline[] = array(
            '$group' => array(
                '_id' => '$multimediaObjectId',
                'data' => array(
                    '$addToSet' => array(
                        'event' => '$event',
                    ),
                ),
            ),
        );

        return $pipeline;
    }

    /**
     * Add element with session sec.
     *
     * @param array $orderSession
     * @param array $element
     * @param int   $indexSec
     *
     * @return array
     */
    protected function addElementWithSessionSec($orderSession, $element, $indexSec)
    {
        $index = 0;
        while (isset($orderSession[$indexSec + $index])) {
            ++$index;
        }
        $orderSession[$indexSec + $index] = $element;

        return $orderSession;
    }

    /**
     * Is live broadcasting.
     *
     * @return bool
     */
    public function isLiveBroadcasting()
    {
        $events = $this->repo->findNowEventSessions();

        return (count($events) > 0);
    }
}
