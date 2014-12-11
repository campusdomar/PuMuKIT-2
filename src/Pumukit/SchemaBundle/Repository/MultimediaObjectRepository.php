<?php

namespace Pumukit\SchemaBundle\Repository;

use Doctrine\ODM\MongoDB\DocumentRepository;
use Pumukit\SchemaBundle\Document\Series;
use Pumukit\SchemaBundle\Document\MultimediaObject;

/**
 * MultimediaObjectRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class MultimediaObjectRepository extends DocumentRepository
{
    /**
   * Find all multimedia objects in a series with given status
   *
   * @param Series $series
   * @param array $status
   * @return ArrayCollection
   */
  public function findWithStatus(Series $series, array $status)
  {
      return $this->createQueryBuilder()
      ->field('series')->references($series)
      ->field('status')->in($status)
      ->getQuery()
      ->execute()
      ->sort(array('rank', 'desc'));
  }

  /**
   * Find multimedia object prototype
   *
   * @param Series $series
   * @param array $status
   * @return MultimediaObject
   */
  public function findPrototype(Series $series)
  {
      return $this->createQueryBuilder()
      ->field('series')->references($series)
      ->field('status')->equals(MultimediaObject::STATUS_PROTOTYPE)
      ->getQuery()
      ->getSingleResult();
  }

  /**
   * Find multimedia objects in a series
   * without the template (prototype)
   *
   * @param Series $series
   * @return ArrayCollection
   */
  public function findWithoutPrototype(Series $series)
  {
      return $this->createQueryBuilder()
      ->field('series')->references($series)
      ->field('status')->notEqual(MultimediaObject::STATUS_PROTOTYPE)
      ->getQuery()
      ->execute()
      ->sort(array('rank', 'desc'));
  }
}
