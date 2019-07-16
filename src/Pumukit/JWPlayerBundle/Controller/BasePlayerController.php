<?php

namespace Pumukit\JWPlayerBundle\Controller;

use Pumukit\BasePlayerBundle\Controller\BasePlayerController as BasePlayerControllero;
use Pumukit\CoreBundle\Controller\PersonalControllerInterface;
use Pumukit\SchemaBundle\Document\MultimediaObject;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class BasePlayerController extends BasePlayerControllero implements PersonalControllerInterface
{
    /**
     * @Route("/videoplayer/{id}", name="pumukit_videoplayer_index", defaults={"show_block": true, "no_channels": true, "track": false} )
     * @Template("PumukitJWPlayerBundle:JWPlayer:player.html.twig")
     *
     * @param Request          $request
     * @param MultimediaObject $multimediaObject
     *
     * @return array|bool|mixed|Response|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function indexAction(Request $request, MultimediaObject $multimediaObject)
    {
        $playerService = $this->get('pumukit_baseplayer.player_service');
        $canBeReproduced = $playerService->canBeReproduced($multimediaObject, false);
        if (!$canBeReproduced) {
            return [
                'object' => $multimediaObject,
            ];
        }

        return $this->doRender($request, $multimediaObject, false);
    }

    /**
     * @Route("/videoplayer/magic/{secret}", name="pumukit_videoplayer_magicindex", defaults={"show_block": true, "no_channels": true, "track": false} )
     * @Template("PumukitJWPlayerBundle:JWPlayer:player.html.twig")
     *
     * @param Request          $request
     * @param MultimediaObject $multimediaObject
     *
     * @return array|bool|mixed|Response|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function magicAction(Request $request, MultimediaObject $multimediaObject)
    {
        $playerService = $this->get('pumukit_baseplayer.player_service');
        $canBeReproduced = $playerService->canBeReproduced($multimediaObject, true);
        if (!$canBeReproduced) {
            return [
                'object' => $multimediaObject,
            ];
        }

        return $this->doRender($request, $multimediaObject, true);
    }

    /**
     * @param Request          $request
     * @param MultimediaObject $multimediaObject
     * @param bool             $isMagicUrl
     *
     * @return array|bool|Response|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function doRender(Request $request, MultimediaObject $multimediaObject, $isMagicUrl = false)
    {
        $embeddedBroadcastService = $this->get('pumukitschema.embeddedbroadcast');
        $password = $request->get('broadcast_password');
        $response = $embeddedBroadcastService->canUserPlayMultimediaObject($multimediaObject, $this->getUser(), $password);
        if ($response instanceof Response) {
            return $response;
        }

        $track = $request->query->has('track_id') ?
            $multimediaObject->getTrackById($request->query->get('track_id')) :
            $multimediaObject->getDisplayTrack();

        if ($track && $track->containsTag('download')) {
            return $this->redirect($track->getUrl());
        }

        if ($url = $multimediaObject->getProperty('externalplayer')) {
            return $this->redirect($url);
        }

        return [
            'autostart' => $request->query->get('autostart', 'false'),
            'intro' => $this->get('pumukit_baseplayer.intro')->getIntroForMultimediaObject($request->query->get('intro'), $multimediaObject->getProperty('intro')),
            'multimediaObject' => $multimediaObject,
            'object' => $multimediaObject,
            'when_dispatch_view_event' => $this->container->getParameter('pumukitplayer.when_dispatch_view_event'),
            'track' => $track,
            'magic_url' => $isMagicUrl,
        ];
    }
}
