<?php

namespace Pumukit\SchemaBundle\Repository;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\ODM\MongoDB\DocumentRepository;
use Pumukit\SchemaBundle\Document\EmbeddedTag;
use Pumukit\SchemaBundle\Document\SeriesType;
use Pumukit\SchemaBundle\Document\Series;
use Pumukit\SchemaBundle\Document\Tag;

/**
 * SeriesRepository.
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class SeriesRepository extends DocumentRepository
{
    /**
     * Find series by tag id.
     *
     * @param Tag|EmbeddedTag $tag
     * @param array           $sort
     * @param int             $limit
     * @param int             $page
     *
     * @return mixed
     */
    public function findWithTag($tag, $sort = array(), $limit = 0, $page = 0)
    {
        $qb = $this->createBuilderWithTag($tag, $sort);

        $qb = $this->addLimitToQueryBuilder($qb, $limit, $page);

        return $qb->getQuery()->execute();
    }

    /**
     * Create QueryBuilder to find series by tag id.
     *
     * @param $tag
     * @param array $sort
     *
     * @return \Doctrine\MongoDB\Query\Builder|mixed
     */
    public function createBuilderWithTag($tag, $sort = array())
    {
        $referencedSeries = $this->getDocumentManager()->getRepository('PumukitSchemaBundle:MultimediaObject')->findSeriesFieldWithTag($tag);

        $qb = $this->createQueryBuilder()->field('_id')->in($referencedSeries->toArray());

        $qb = $this->addSortToQueryBuilder($qb, $sort);

        return $qb;
    }

    /**
     * Find one series with tag.
     *
     * @param Tag|EmbeddedTag $tag
     *
     * @return array|null|object
     */
    public function findOneWithTag($tag)
    {
        $referencedOneSeries = $this->getDocumentManager()->getRepository('PumukitSchemaBundle:MultimediaObject')->findOneSeriesFieldWithTag($tag);

        return $this->createQueryBuilder()->field('_id')->equals($referencedOneSeries)->getQuery()->getSingleResult();
    }

    /**
     * Find series with any tag.
     *
     * @param array $tags
     * @param array $sort
     * @param int   $limit
     * @param int   $page
     *
     * @return ArrayCollection
     */
    public function findWithAnyTag($tags, $sort = array(), $limit = 0, $page = 0)
    {
        $referencedSeries = $this->getDocumentManager()->getRepository('PumukitSchemaBundle:MultimediaObject')->findSeriesFieldWithAnyTag($tags);

        $qb = $this->createQueryBuilder()->field('_id')->in($referencedSeries->toArray());

        $qb = $this->addSortAndLimitToQueryBuilder($qb, $sort, $limit, $page);

        return $qb->getQuery()->execute();
    }

    /**
     * Find series with all tags.
     *
     * @param array $tags
     * @param array $sort
     * @param int   $limit
     * @param int   $page
     *
     * @return ArrayCollection
     */
    public function findWithAllTags($tags, $sort = array(), $limit = 0, $page = 0)
    {
        $referencedSeries = $this->getDocumentManager()->getRepository('PumukitSchemaBundle:MultimediaObject')->findSeriesFieldWithAllTags($tags);

        $qb = $this->createQueryBuilder()->field('_id')->in($referencedSeries->toArray());

        $qb = $this->addSortAndLimitToQueryBuilder($qb, $sort, $limit, $page);

        return $qb->getQuery()->execute();
    }

    /**
     * Find one series with all tags.
     *
     * @param array $tags
     *
     * @return Series
     */
    public function findOneWithAllTags($tags)
    {
        $referencedOneSeries = $this->getDocumentManager()->getRepository('PumukitSchemaBundle:MultimediaObject')->findOneSeriesFieldWithAllTags($tags);

        return $this->createQueryBuilder()->field('_id')->equals($referencedOneSeries)->getQuery()->getSingleResult();
    }

    /**
     * Find series without tag.
     *
     * @param Tag|EmbeddedTag $tag
     * @param array           $sort
     * @param int             $limit
     * @param int             $page
     *
     * @return ArrayCollection
     */
    public function findWithoutTag($tag, $sort = array(), $limit = 0, $page = 0)
    {
        $referencedSeries = $this->getDocumentManager()->getRepository('PumukitSchemaBundle:MultimediaObject')->findSeriesFieldWithTag($tag);

        $qb = $this->createQueryBuilder()->field('_id')->notIn($referencedSeries->toArray());

        $qb = $this->addSortAndLimitToQueryBuilder($qb, $sort, $limit, $page);

        return $qb->getQuery()->execute();
    }

    /**
     * Find one series without tag.
     *
     * @param Tag|EmbeddedTag $tag
     *
     * @return array|null|object
     */
    public function findOneWithoutTag($tag)
    {
        $referencedSeries = $this->getDocumentManager()->getRepository('PumukitSchemaBundle:MultimediaObject')->findSeriesFieldWithTag($tag);

        return $this->createQueryBuilder()->field('_id')->notIn($referencedSeries->toArray())->getQuery()->getSingleResult();
    }

    /**
     * Find series without all tags.
     *
     * @param array tags
     * @param array $sort
     *
     * @return mixed
     */
    public function findWithoutAllTags($tags, $sort = array(), $limit = 0, $page = 0)
    {
        $referencedSeries = $this->getDocumentManager()->getRepository('PumukitSchemaBundle:MultimediaObject')->findSeriesFieldWithAllTags($tags);

        $qb = $this->createQueryBuilder()->field('_id')->notIn($referencedSeries->toArray());

        $qb = $this->addSortAndLimitToQueryBuilder($qb, $sort, $limit, $page);

        return $qb->getQuery()->execute();
    }

    /**
     * Find series by pic id.
     *
     * @param string $picId
     *
     * @return array|null|object
     */
    public function findByPicId($picId)
    {
        return $this->createQueryBuilder()->field('pics._id')->equals(new \MongoId($picId))->getQuery()->getSingleResult();
    }

    /**
     * Find series by person id.
     *
     * @param string $personId
     *
     * @return mixed
     */
    public function findSeriesByPersonId($personId)
    {
        $repoMmobj = $this->getDocumentManager()->getRepository('PumukitSchemaBundle:MultimediaObject');

        $referencedSeries = $repoMmobj->findSeriesFieldByPersonId($personId);

        return $this->createQueryBuilder()->field('_id')->in($referencedSeries->toArray())->getQuery()->execute();
    }

    /**
     * Create builder to Find series
     * by person id and role cod.
     *
     * @param $personId
     * @param $roleCod
     * @param array $sort
     * @param int   $limit
     * @param int   $page
     *
     * @return \Doctrine\MongoDB\Query\Builder
     */
    public function createBuilderByPersonIdAndRoleCod($personId, $roleCod, $sort = array(), $limit = 0, $page = 0)
    {
        $repoMmobj = $this->getDocumentManager()->getRepository('PumukitSchemaBundle:MultimediaObject');
        $referencedSeries = $repoMmobj->findSeriesFieldByPersonIdAndRoleCod($personId, $roleCod);

        return $this->createQueryBuilder()->field('_id')->in($referencedSeries->toArray());
    }

    /**
     * Find series by person id and role cod.
     *
     * @param $personId
     * @param $roleCod
     * @param array $sort
     * @param int   $limit
     * @param int   $page
     *
     * @return mixed
     */
    public function findByPersonIdAndRoleCod($personId, $roleCod, $sort = array(), $limit = 0, $page = 0)
    {
        $qb = $this->createBuilderByPersonIdAndRoleCod($personId, $roleCod, $sort, $limit, $page);

        return $qb->getQuery()->execute();
    }

    /**
     * Find series by person id and role cod or groups.
     *
     * @param $personId
     * @param $roleCod
     * @param $groups
     *
     * @return mixed
     */
    public function findByPersonIdAndRoleCodOrGroups($personId, $roleCod, $groups)
    {
        $repoMmobj = $this->getDocumentManager()->getRepository('PumukitSchemaBundle:MultimediaObject');
        $referencedSeries = $repoMmobj->findSeriesFieldByPersonIdAndRoleCodOrGroups($personId, $roleCod, $groups);

        return $this->createQueryBuilder()->field('_id')->in($referencedSeries->toArray())->getQuery()->execute();
    }

    /**
     * Find series by person id and role cod or groups sorted Query Builder.
     *
     * @param $personId
     * @param $roleCod
     * @param $groups
     * @param array $sort
     * @param int   $limit
     * @param int   $page
     *
     * @return \Doctrine\MongoDB\Query\Builder|mixed
     */
    public function findByPersonIdAndRoleCodOrGroupsSortedQueryBuilder($personId, $roleCod, $groups, $sort = array(), $limit = 0, $page = 0)
    {
        $repoMmobj = $this->getDocumentManager()->getRepository('PumukitSchemaBundle:MultimediaObject');
        $referencedSeries = $repoMmobj->findSeriesFieldByPersonIdAndRoleCodOrGroups($personId, $roleCod, $groups);

        $qb = $this->createQueryBuilder()->field('_id')->in($referencedSeries->toArray());

        $qb = $this->addSortAndLimitToQueryBuilder($qb, $sort, $limit, $page);

        return $qb;
    }

    /**
     * Find series by person id and role cod or groups sorted Query.
     *
     * @param $personId
     * @param $roleCod
     * @param $groups
     * @param array $sort
     * @param int   $limit
     * @param int   $page
     *
     * @return \Doctrine\MongoDB\Query\Query
     */
    public function findByPersonIdAndRoleCodOrGroupsSortedQuery($personId, $roleCod, $groups, $sort = array(), $limit = 0, $page = 0)
    {
        $qb = $this->findByPersonIdAndRoleCodOrGroupsSortedQueryBuilder($personId, $roleCod, $groups, $sort, $limit, $page);

        return $qb->getQuery();
    }

    /**
     * Find series by person id and role cod or groups sorted.
     *
     * @param $personId
     * @param $roleCod
     * @param $groups
     * @param array $sort
     * @param int   $limit
     * @param int   $page
     *
     * @return mixed
     */
    public function findByPersonIdAndRoleCodOrGroupsSorted($personId, $roleCod, $groups, $sort = array(), $limit = 0, $page = 0)
    {
        $query = $this->findByPersonIdAndRoleCodOrGroupsSortedQuery($personId, $roleCod, $groups, $sort, $limit, $page);

        return $query->execute();
    }

    /**
     * Find series with given series type.
     *
     * @param SeriesType $series_type
     *
     * @return mixed
     *
     * @throws \Doctrine\ODM\MongoDB\MongoDBException
     */
    public function findBySeriesType(SeriesType $series_type)
    {
        return $this->createQueryBuilder()->field('series_type')->references($series_type)->getQuery()->execute();
    }

    /**
     * Count number of series in the repo.
     *
     * @return mixed
     */
    public function count()
    {
        return $this->createQueryBuilder()->count()->getQuery()->execute();
    }

    /**
     * Count number of series in the repo.
     *
     * @return mixed
     */
    public function countPublic()
    {
        return $this->getDocumentManager()->getRepository('PumukitSchemaBundle:MultimediaObject')->createStandardQueryBuilder()->distinct('series')->getQuery()->execute()->count();
    }

    /**
     * Find series with tag and series type.
     *
     * @param $tag
     * @param $seriesType
     * @param array $sort
     * @param int   $limit
     * @param int   $page
     *
     * @return mixed
     */
    public function findWithTagAndSeriesType($tag, $seriesType, $sort = array(), $limit = 0, $page = 0)
    {
        $qb = $this->createBuilderWithTagAndSeriesType($tag, $seriesType, $sort);

        $qb = $this->addLimitToQueryBuilder($qb, $limit, $page);

        return $qb->getQuery()->execute();
    }

    /**
     * Create QueryBuilder to find series with tag and series type.
     *
     * @param $tag
     * @param $seriesType
     * @param array $sort
     *
     * @return mixed
     */
    public function createBuilderWithTagAndSeriesType($tag, $seriesType, $sort = array())
    {
        $referencedSeries = $this->getDocumentManager()->getRepository('PumukitSchemaBundle:MultimediaObject')->findSeriesFieldWithTag($tag);

        $qb = $this->createQueryBuilder()->field('_id')->in($referencedSeries->toArray())->field('series_type')->references($seriesType);

        $qb = $this->addSortToQueryBuilder($qb, $sort);

        return $qb;
    }

    /**
     * Find series with the same propertyName.
     *
     * @param $propertyName
     * @param $propertyValue
     *
     * @return array|null|object
     */
    public function findOneBySeriesProperty($propertyName, $propertyValue)
    {
        return $this->createQueryBuilder()->field('properties.'.$propertyName)->equals($propertyValue)->getQuery()->getSingleResult();
    }

    /**
     * Find by EmbeddedBroadcast type Query Builder.
     *
     * @param string $type
     * @param array  $sort
     * @param int    $limit
     * @param int    $page
     *
     * @return \Doctrine\MongoDB\Query\Builder|mixed
     */
    public function findByEmbeddedBroadcastTypeQueryBuilder($type = '', $sort = array(), $limit = 0, $page = 0)
    {
        $repoMmobj = $this->getDocumentManager()->getRepository('PumukitSchemaBundle:MultimediaObject');
        $referencedSeries = $repoMmobj->findSeriesFieldByEmbeddedBroadcastType($type);

        $qb = $this->createQueryBuilder()->field('_id')->in($referencedSeries->toArray());

        $qb = $this->addSortAndLimitToQueryBuilder($qb, $sort, $limit, $page);

        return $qb;
    }

    /**
     * Find by EmbeddedBroadcast type Query.
     *
     * @param string $type
     * @param array  $sort
     * @param int    $limit
     * @param int    $page
     *
     * @return \Doctrine\MongoDB\Query\Query
     */
    public function findByEmbeddedBroadcastTypeQuery($type = '', $sort = array(), $limit = 0, $page = 0)
    {
        $qb = $this->findByEmbeddedBroadcastTypeQueryBuilder($type, $sort, $limit, $page);

        return $qb->getQuery();
    }

    /**
     * Find by EmbeddedBroadcast type.
     *
     * @param string $type
     * @param array  $sort
     * @param int    $limit
     * @param int    $page
     *
     * @return mixed
     */
    public function findByEmbeddedBroadcastType($type = '', $sort = array(), $limit = 0, $page = 0)
    {
        $query = $this->findByEmbeddedBroadcastTypeQuery($type, $sort, $limit, $page);

        return $query->execute();
    }

    /**
     * Find by embedded broadcast type and groups Query Builder.
     *
     * @param string $type
     * @param array  $groups
     * @param array  $sort
     * @param int    $limit
     * @param int    $page
     *
     * @return \Doctrine\MongoDB\Query\Builder|mixed
     */
    public function findByEmbeddedBroadcastTypeAndGroupsQueryBuilder($type = '', $groups = array(), $sort = array(), $limit = 0, $page = 0)
    {
        $repoMmobj = $this->getDocumentManager()->getRepository('PumukitSchemaBundle:MultimediaObject');
        $referencedSeries = $repoMmobj->findSeriesFieldByEmbeddedBroadcastTypeAndGroups($type, $groups);

        $qb = $this->createQueryBuilder()->field('_id')->in($referencedSeries->toArray());

        $qb = $this->addSortAndLimitToQueryBuilder($qb, $sort, $limit, $page);

        return $qb;
    }

    /**
     * Find by embedded broadcast type and groups Query.
     *
     * @param string $type
     * @param array  $groups
     * @param array  $sort
     * @param int    $limit
     * @param int    $page
     *
     * @return \Doctrine\MongoDB\Query\Query
     */
    public function findByEmbeddedBroadcastTypeAndGroupsQuery($type = '', $groups = array(), $sort = array(), $limit = 0, $page = 0)
    {
        $qb = $this->findByEmbeddedBroadcastTypeAndGroupsQueryBuilder($type, $groups, $sort, $limit, $page);

        return $qb->getQuery();
    }

    /**
     * Find by embedded broadcast type and groups.
     *
     * @param string $type
     * @param array  $groups
     * @param array  $sort
     * @param int    $limit
     * @param int    $page
     *
     * @return mixed
     */
    public function findByEmbeddedBroadcastTypeAndGroups($type = '', $groups = array(), $sort = array(), $limit = 0, $page = 0)
    {
        $query = $this->findByEmbeddedBroadcastTypeAndGroupsQuery($type, $groups, $sort, $limit, $page);

        return $query->execute();
    }

    /**
     * Find by title with locale query builder.
     *
     * @param string $title
     * @param string $locale
     * @param array  $sort
     * @param int    $limit
     * @param int    $page
     *
     * @return \Doctrine\MongoDB\Query\Builder|mixed
     */
    public function findByTitleWithLocaleQueryBuilder($title = '', $locale = 'en', $sort = array(), $limit = 0, $page = 0)
    {
        $qb = $this->createQueryBuilder()->field('title.'.$locale)->equals(new \MongoRegex(sprintf('/%s/i', $title)));

        $qb = $this->addSortAndLimitToQueryBuilder($qb, $sort, $limit, $page);

        return $qb;
    }

    /**
     * Find by title with locale query.
     *
     * @param string $title
     * @param string $locale
     * @param array  $sort
     * @param int    $limit
     * @param int    $page
     *
     * @return \Doctrine\MongoDB\Query\Query
     */
    public function findByTitleWithLocaleQuery($title = '', $locale = 'en', $sort = array(), $limit = 0, $page = 0)
    {
        $qb = $this->findByTitleWithLocaleQueryBuilder($title, $locale, $sort, $limit, $page);

        return $qb->getQuery();
    }

    /**
     * Find by title with locale.
     *
     * @param string $title
     * @param string $locale
     * @param array  $sort
     * @param int    $limit
     * @param int    $page
     *
     * @return mixed
     */
    public function findByTitleWithLocale($title = '', $locale = 'en', $sort = array(), $limit = 0, $page = 0)
    {
        $query = $this->findByTitleWithLocaleQuery($title, $locale, $sort, $limit, $page);

        return $query->execute();
    }

    /**
     * Add limit (and page) to Query Builder.
     *
     * @param $qb
     * @param int $limit
     * @param int $page
     *
     * @return mixed
     */
    private function addLimitToQueryBuilder($qb, $limit = 0, $page = 0)
    {
        if ($limit > 0) {
            $qb->limit($limit)->skip($limit * $page);
        }

        return $qb;
    }

    /**
     * Add sort to Query Builder.
     *
     * @param $qb
     * @param array $sort
     *
     * @return mixed
     */
    private function addSortToQueryBuilder($qb, $sort = array())
    {
        if (0 !== count($sort)) {
            $qb->sort($sort);
        }

        return $qb;
    }

    /**
     * Add sort and limit (and page) to Query Builder.
     *
     * @param $qb
     * @param array $sort
     * @param int   $limit
     * @param int   $page
     *
     * @return mixed
     */
    private function addSortAndLimitToQueryBuilder($qb, $sort = array(), $limit = 0, $page = 0)
    {
        $qb = $this->addSortToQueryBuilder($qb, $sort);
        $qb = $this->addLimitToQueryBuilder($qb, $limit, $page);

        return $qb;
    }

    /**
     * @param $user
     * @param bool $onlyAdminSeries
     *
     * @return array
     *
     * @throws \Doctrine\ODM\MongoDB\MongoDBException
     */
    public function findUserSeries($user, $onlyAdminSeries = false)
    {
        $dm = $this->getDocumentManager();

        /* Find user series (properties.owners) */
        $seriesCollection = $dm->getDocumentCollection('PumukitSchemaBundle:Series');

        if (($permissionProfile = $user->getPermissionProfile()) && $permissionProfile->isGlobal() && !$onlyAdminSeries) {
            $group = array('_id' => array('id' => '$_id', 'title' => '$title'));
            $command = array(array('$group' => $group));

            return $seriesCollection->aggregate($command, array('cursor' => array()))->toArray();
        }

        $match = [];
        $match['properties.owners'] = array('$in' => array($user->getId()));
        $group = array('_id' => array('id' => '$_id', 'title' => '$title'));

        $command = array(array('$match' => $match), array('$group' => $group));
        $aSeries = $seriesCollection->aggregate($command, array('cursor' => array()))->toArray();

        /* Find mmo user groups */
        $mmoCollection = $dm->getDocumentCollection('PumukitSchemaBundle:MultimediaObject');

        $groups = [];
        foreach ($user->getGroups() as $group) {
            $groups[] = new \MongoId($group->getId());
        }

        $match = [];
        $unwind = array('$unwind' => '$groups');
        $match['groups'] = array('$in' => $groups);
        $group = array('_id' => array('id' => '$series', 'title' => '$seriesTitle'));

        $command = array($unwind, array('$match' => $match), array('$group' => $group));
        $aMMO = $mmoCollection->aggregate($command, array('cursor' => array()))->toArray();

        $aSeries = array_merge($aSeries, $aMMO);
        usort($aSeries, function ($a, $b) {
            return ($a['_id']['title'] <= $b['_id']['title']) ? -1 : 1;
        });

        return $aSeries;
    }

    /**
     * Count number of multimedia objects by series.
     *
     * @deprecated Use MultimediaObjectRepository::countMmobjsBySeries
     *
     * @param array $seriesList A key/value hash where the key is the series id (string) and the value is the count
     *
     * @return mixed
     */
    public function countMmobjsBySeries($seriesList = array())
    {
        return $this->getDocumentManager()
            ->getRepository('PumukitSchemaBundle:MultimediaObject')
            ->countMmobjsBySeries($seriesList);
    }

    /**
     * @param Series $series
     *
     * @return mixed
     */
    public function getMultimediaObjects(Series $series)
    {
        return $this->getDocumentManager()
            ->getRepository('PumukitSchemaBundle:MultimediaObject')
            ->findWithoutPrototype($series);
    }

    /**
     * @param Series $series
     *
     * @return mixed
     */
    public function countMultimediaObjects(Series $series)
    {
        return $this->getDocumentManager()
            ->getRepository('PumukitSchemaBundle:MultimediaObject')
            ->countWithoutPrototype($series);
    }
}
