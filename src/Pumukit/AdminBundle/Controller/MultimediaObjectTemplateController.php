<?php

namespace Pumukit\AdminBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Pumukit\SchemaBundle\Document\Series;
use Pumukit\SchemaBundle\Document\MultimediaObject;

class MultimediaObjectTemplateController extends MultimediaObjectController
{
    /**
   * Edit Multimedia Object Template
   */
  public function editAction(Request $request)
  {
      $config = $this->getConfiguration();

      // TODO VALIDATE SERIES AND ROLES
      $roles = $this->getRoles();
      $series = $this->getSeries($request);

      $parentTags = $this->getParentTags();
      $mmtemplate = $this->getMultimediaObjectTemplate($series);

      $formMeta = $this->createForm($config->getFormType().'_meta', $mmtemplate);
      //$formPub = $this->createForm($config->getFormType() . '_pub', $resource);

      //$pubChannelTags = $this->getTagsByCod('PUBCHANNELS', true);
      $pubDecisionsTags = $this->getTagsByCod('PUBDECISIONS', true);

      $view = $this
      ->view()
      ->setTemplate($config->getTemplate('edit.html'))
      ->setData(array(
              'mmtemplate'    => $mmtemplate,
              'form_meta'      => $formMeta->createView(),
              'series'        => $series,
              'roles'         => $roles,
              'pub_decisions' => $pubDecisionsTags,
          'parent_tags'   => $parentTags,
              ))
      ;

      return $this->handleView($view);
  }

  // TODO
  /**
   * Display the form for editing or update the resource.
   */
  public function updatemetaAction(Request $request)
  {
      $config = $this->getConfiguration();

      // TODO VALIDATE SERIES and roles
      $series = $this->getSeries($request);
      $roles = $this->getRoles();
      $parentTags = $this->getParentTags();
      $mmtemplate = $this->getMultimediaObjectTemplate($series);

      $formMeta = $this->createForm($config->getFormType().'_meta', $mmtemplate);

      //$pubChannelsTags = $this->getTagsByCod('PUBCHANNELS', true);
      $pubDecisionsTags = $this->getTagsByCod('PUBDECISIONS', true);

      $method = $request->getMethod();
      if (in_array($method, array('POST', 'PUT', 'PATCH')) &&
      $formMeta->submit($request, !$request->isMethod('PATCH'))->isValid()) {
          $this->domainManager->update($mmtemplate);

          if ($config->isApiRequest()) {
              return $this->handleView($this->view($formMeta));
          }

          return new JsonResponse(array('mmtemplate' => 'updatemeta'));
      }

      if ($config->isApiRequest()) {
          return $this->handleView($this->view($formMeta));
      }

      $view = $this
      ->view()
      ->setTemplate($config->getTemplate('edit.html'))
      ->setData(array(
              'mm'            => $resource,
              'form_meta'     => $formMeta->createView(),
          'series'        => $series,
          'roles'         => $roles,
          'pub_decisions' => $pubDecisionsTags,
          'parent_tags'   => $parentTags,
              ))
      ;

      return $this->handleView($view);
  }

  /**
   * Get multimedia object template from series
   */
  public function getMultimediaObjectTemplate(Series $series)
  {
      $mmtemplate = null;
      if (!isset($series)) {
          return $mmtemplate;
      }
      foreach ($series->getMultimediaObjects() as $mmobj) {
          if (MultimediaObject::STATUS_PROTOTYPE == $mmobj->getStatus()) {
              $mmtemplate = $mmobj;
              break;
          }
      }

      return $mmtemplate;
  }
}
