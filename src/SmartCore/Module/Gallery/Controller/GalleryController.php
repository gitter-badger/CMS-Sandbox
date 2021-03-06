<?php

namespace SmartCore\Module\Gallery\Controller;

use Knp\RadBundle\Controller\Controller;
use SmartCore\Bundle\CMSBundle\Module\NodeTrait;
use Symfony\Component\HttpFoundation\Response;

class GalleryController extends Controller
{
    use NodeTrait;

    /**
     * @var int
     */
    protected $gallery_id;

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction()
    {
        if (null === $this->gallery_id) {
            return new Response('Module Gallery not yet configured. Node: ' . $this->node->getId() . '<br />');
        }

        /** @var \Doctrine\ORM\EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        $albums = $em->getRepository('GalleryModule:Album')->findBy(['is_enabled' => true], ['id' => 'DESC']);

        $this->node->addFrontControl('manage_gallery', [
            'default' => true,
            'title'   => 'Управление фотогалереей',
            'uri'     => $this->generateUrl('smart_module.gallery.admin_gallery', ['id' => $this->gallery_id]),
        ]);

        return $this->render('GalleryModule::index.html.twig', [
            'albums'  => $albums,
        ]);
    }

    /**
     * @param int $id
     * @return Response
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function albumAction($id)
    {
        /** @var \Doctrine\ORM\EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        $album = $em->getRepository('GalleryModule:Album')->find($id);

        if (empty($album) or $this->gallery_id != $album->getGallery()->getId() or $album->isDisabled()) {
            throw $this->createNotFoundException();
        }

        $this->node->addFrontControl('manage_album', [
            'default' => true,
            'title'   => 'Редактировать фотографии',
            'uri'     => $this->generateUrl('smart_module.gallery.admin_album', ['id' => $album->getId(), 'gallery_id' => $this->gallery_id]),
        ]);

        $this->get('cms.breadcrumbs')->add($album->getId(), $album->getTitle());

        $photos = $em->getRepository('GalleryModule:Photo')->findBy(['album' => $album], ['id' => 'DESC']);

        return $this->render('GalleryModule::photos.html.twig', [
            'photos'  => $photos,
        ]);
    }
}
