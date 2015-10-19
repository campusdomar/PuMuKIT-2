<?php

namespace Pumukit\WebTVBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Pagerfanta\Adapter\DoctrineODMMongoDBAdapter;
use Pagerfanta\Pagerfanta;
use Pumukit\SchemaBundle\Document\Series;
use Pumukit\SchemaBundle\Document\MultimediaObject;
use Pumukit\SchemaBundle\Document\Tag;

class SearchController extends Controller
{

  /**
   * @Route("/searchseries")
   * @Template("PumukitWebTVBundle:Search:index.html.twig")
   */
  public function seriesAction(Request $request)
  {
      $this->get('pumukit_web_tv.breadcrumbs')->addList('Series search', 'pumukit_webtv_search_series');

      $search_found = $request->query->get('search');
      $start_found = $request->query->get('start');
      $end_found = $request->query->get('end');

      $repository_series = $this->get('doctrine_mongodb')->getRepository('PumukitSchemaBundle:Series');

      $queryBuilder = $repository_series->createQueryBuilder();

      if ($search_found != '') {
          $queryBuilder->field('$text')->equals(array('$search' => $search_found));
      }

      if ($start_found != 'All' && $start_found != '') {
          $start = \DateTime::createFromFormat('d/m/Y', $start_found);
          $queryBuilder->field('public_date')->gt($start);
      }

      if ($end_found != 'All' && $end_found != '') {
          $end = \DateTime::createFromFormat('d/m/Y', $end_found);
          $queryBuilder->field('public_date')->lt($end);
      }

      $pagerfanta = $this->createPager($queryBuilder, $request->query->get('page', 1));

      return array('type' => 'series',
         'objects' => $pagerfanta, );
  }

  /**
   * @Route("/searchmultimediaobjects")
   * @Template("PumukitWebTVBundle:Search:index.html.twig")
   */
  public function multimediaObjectsAction(Request $request)
  {
      $this->get('pumukit_web_tv.breadcrumbs')->addList('Multimedia object search', 'pumukit_webtv_search_multimediaobjects');

      $tag_search = new Tag();

      $search_found = $request->query->get('search');
      $tag_found = $request->query->get('tags');
      $type_found = $request->query->get('type');
      $duration_found = $request->query->get('duration');
      $start_found = $request->query->get('start');
      $end_found = $request->query->get('end');

      $repository_multimediaObjects = $this->get('doctrine_mongodb')->getRepository('PumukitSchemaBundle:MultimediaObject');
      $repository_tags = $this->get('doctrine_mongodb')->getRepository('PumukitSchemaBundle:Tag');

      $tags = $repository_tags->findall();

      for ($i = 0;$i < count($tags);++$i) {
          if ($tags[$i]->getTitle() == $tag_found) {
              $tag_search = $tags[$i];
          }
      }

      $queryBuilder = $repository_multimediaObjects->createStandardQueryBuilder();

      if ($search_found != '') {
          $queryBuilder->field('$text')->equals(array('$search' => $search_found));
      }

      if ($tag_found != 'All' && $tag_found != '') {
          $queryBuilder->field('tags._id')->equals(new \MongoId($tag_search->getId()));
      }

      if ($type_found != 'All' && $type_found != '') {
          $queryBuilder->field('tracks.only_audio')->equals($type_found == 'Audio');
      }

      if ($duration_found != 'All' && $duration_found != '') {
          if ($duration_found == '-5') {
              $queryBuilder->field('tracks.duration')->lte(300);
          }
          if ($duration_found == '-10') {
              $queryBuilder->field('tracks.duration')->lte(600);
          }
          if ($duration_found == '-30') {
              $queryBuilder->field('tracks.duration')->lte(1800);
          }
          if ($duration_found == '-60') {
              $queryBuilder->field('tracks.duration')->lte(3600);
          }
          if ($duration_found == '+60') {
              $queryBuilder->field('tracks.duration')->gt(3600);
          }
      }

      if ($start_found != 'All' && $start_found != '') {
          $start = \DateTime::createFromFormat('d/m/Y', $start_found);
          $queryBuilder->field('record_date')->gt($start);
      }

      if ($end_found != 'All' && $end_found != '') {
          $end = \DateTime::createFromFormat('d/m/Y', $end_found);
          $queryBuilder->field('record_date')->lt($end);
      }

      $pagerfanta = $this->createPager($queryBuilder, $request->query->get('page', 1));

      return array('type' => 'multimediaObject',
         'objects' => $pagerfanta,
         'tags' => $tags,
         'tag_found' => $tag_found,
         'type_found' => $type_found,
         'duration_found' => $duration_found, );
    }

    private function createPager($objects, $page)
    {
        $limit = 10;
        if ($this->container->hasParameter('limit_objs_bytag')){
            $limit = $this->container->getParameter('limit_objs_bytag');
        }
        $adapter = new DoctrineODMMongoDBAdapter($objects);
        $pagerfanta = new Pagerfanta($adapter);
        $pagerfanta->setMaxPerPage($limit);
        $pagerfanta->setCurrentPage($page);

        return $pagerfanta;
    }
}
