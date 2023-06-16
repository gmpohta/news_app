<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Cookie;
use App\Entity\Article;
use App\Entity\File;
use App\Type\ArticleType;

class MainController extends AbstractController
{
    
    private EntityManagerInterface $entityManager;

    public function __construct(
        EntityManagerInterface $em, 
    ) {
        $this->em = $em;
    }

    #[Route('/', name:'create_new_article')] 
    public function createAction(Request $request)
    {
        $article = new Article();
        $form = $this->createForm(ArticleType::class, $article);
        
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->persist($article);
            $this->em->flush();

            $headers = ['alg'=>'HS256','typ'=>'JWT'];
            $payload = ['id'=>$article->getId(), 'exp'=>(time() + 3600*3)];
            $this->addFlash('accessToken', $this->generateJwt($headers, $payload));

            return $this->redirectToRoute('render_article', [
                'id'=>$article->getId()
            ]);
        }

        return $this->render('new-page.html.twig',[
            'form' => $form->createView()
        ]);
    }

    #[Route('/edit/{id}', name:'edit_article')] 
    public function editAction(int $id, Request $request)
    {   
        
        if ($request->cookies->get('accessToken')) {
            $decodedJWT = $this->isJwtValid($request->cookies->get('accessToken'), $secret = 'secret');        
            $idFromToken = $decodedJWT['payload']['id'];
            $isValid = $decodedJWT['isValid'];
        }

        if (
            $request->cookies->get('accessToken') && 
            $idFromToken == $id &&
            $isValid
        ) {
            $article = $this->em
                ->getRepository(Article::class)
                ->findOneBy(['id' => $id]);

            $form = $this->createForm(ArticleType::class, $article);
            
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $this->em->persist($article);
                $this->em->flush();

                return $this->redirectToRoute('render_article', [
                    'id'=>$article->getId()
                ]);
            } 

            return $this->render('new-page.html.twig',[
                'form' => $form->createView()
            ]);
        }
        
        return $this->redirectToRoute('create_new_article');
    }

    #[Route('/{id}', name: 'render_article', requirements: ['id' => '\d+'])] 
    public function renderAction(int $id, Request $request)
    {
        $session = $request->getSession();
        $accessToken = $session->getFlashBag()->get('accessToken');
        $accessToken = $accessToken ? $accessToken[0] : null;
        
        $mayEdit = false;

        if ($request->cookies->get('accessToken')) {
            $decodedJWT = $this->isJwtValid($request->cookies->get('accessToken'), $secret = 'secret');        
            $idFromToken = $decodedJWT['payload']['id'];
            $isValid = $decodedJWT['isValid'];
        }

        if (
            $request->cookies->get('accessToken') && 
            $idFromToken == $id &&
            $isValid
        ) {
            $mayEdit = true;
        }
        
        if ($accessToken) {
            $decodedJWT = $this->isJwtValid($accessToken, $secret = 'secret');        
            $idFromToken = $decodedJWT['payload']['id'];
            $isValid = $decodedJWT['isValid'];
        }
        
        if (
            $accessToken && 
            $idFromToken == $id &&
            $isValid
        ) {
            $mayEdit = true;
        }

        $article = $this->em
            ->getRepository(Article::class)
            ->findOneBy(['id' => $id]);

        if (!$article) {
            throw $this->createNotFoundException(sprintf('Article with id "%s" not found.', $id));
        }

        return $this->render('page.html.twig', [
            'article' => $article,
            'mayEdit' => $mayEdit,
            'accessToken' => $accessToken,
        ]);
    }

    #[Route('/upload', name:'upload_file')]
    public function uploadFileAction(Request $request): JsonResponse
    {
        $files = array_values($request->files->all());
        
        if (empty($files)) {
            return new JsonResponse(
                [], 400
            );
        }

        $fileToUpload = new File();

        $fileToUpload->setFile($files[0]);
        $this->em->persist($fileToUpload);
        $this->em->flush();

        return new JsonResponse(
            $fileToUpload->getWebPath(), 200
        );
    }

    function generateJwt($headers, $payload, $secret = 'secret') {
        $headers_encoded = base64_encode(json_encode($headers));
        
        $payload_encoded = base64_encode(json_encode($payload));
        
        $signature = hash_hmac('SHA256', "$headers_encoded.$payload_encoded", $secret, true);
        $signature_encoded = base64_encode($signature);
        
        $jwt = "$headers_encoded.$payload_encoded.$signature_encoded";
        
        return $jwt;
    }

    function isJwtValid($jwt, $secret = 'secret') {
        // split the jwt
        $tokenParts = explode('.', $jwt);
        $header = base64_decode($tokenParts[0]);
        $payload = base64_decode($tokenParts[1]);
        $signature_provided = $tokenParts[2];
    
        // check the expiration time - note this will cause an error if there is no 'exp' claim in the jwt
        $expiration = json_decode($payload)->exp;
        $is_token_expired = ($expiration - time()) < 0;
    
        // build a signature based on the header and payload using the secret
        $base64_url_header = base64_encode($header);
        $base64_url_payload = base64_encode($payload);
        $signature = hash_hmac('SHA256', $base64_url_header . "." . $base64_url_payload, $secret, true);
        $base64_url_signature = base64_encode($signature);
    
        // verify it matches the signature provided in the jwt
        $is_signature_valid = ($base64_url_signature === $signature_provided);
    
        if ($is_token_expired || !$is_signature_valid) {
            return ['isValid' => FALSE];
        } else {
            return ['isValid' => TRUE, 'payload' => json_decode($payload, TRUE)];
        }
    }
}
