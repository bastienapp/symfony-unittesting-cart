<?php

namespace App\Controller;

use App\Repository\CartRepository;
use App\Service\CartManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CartController extends AbstractController
{
    /**
     * @Route("/cart", name="cart")
     */
    public function index(CartRepository $cartRepository, CartManager $cartManager): Response
    {
        $cart = $cartRepository->find(1);

        return $this->render('cart/index.html.twig', [
            'cart' => $cart,
            'cartManager' => $cartManager
        ]);
    }
}
