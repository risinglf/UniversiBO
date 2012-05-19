<?php

namespace UniversiBO\Bundle\WebsiteBundle\Controller;

use UniversiBO\Bundle\LegacyBundle\Entity\Facolta;

use Symfony\Component\HttpFoundation\Response;


use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/**
 * @Route("/faculty");
 */
class FacultyController extends Controller
{
    /**
     * @Route("/{id}", name="faculty_show")
     * @Template()
     */
    public function showAction($id)
    {
        $acl = $this->get('universibo_legacy.acl');
        $channelRepo = $this->get('universibo_legacy.repository.facolta');

        $scontext = $this->get('security.context');

        $user = $scontext->isGranted('IS_AUTHENTICATED_FULLY') ? $scontext
        ->getToken()->getUser() : null;

        $channel = $channelRepo->find($id);

        if(!$channel instanceof Facolta) {
            throw $this->createNotFoundException('Channel not found');
        }

        if (!$acl->canRead($user, $channel)) {
            $response = new Response();
            $response->setStatusCode(403);
            $response->setContent('403 Forbidden');

            return $response;
        }


        return array('faculty' => $channel);
    }
}