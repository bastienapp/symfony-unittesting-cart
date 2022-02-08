<?php

namespace App\Service;

use App\Entity\Cart;
use App\Entity\CartItem;
use App\Entity\Product;
use App\Repository\CartItemRepository;
use App\Repository\CartRepository;
use App\Repository\ProductRepository;
use Doctrine\Common\Cache\Psr6\InvalidArgument;
use InvalidArgumentException;

class CartManager
{
    private ProductRepository $productRepository;
    private CartRepository $cartRepository;
    private CartItemRepository $cartItemRepository;

    public function __construct(ProductRepository $productRepository, CartRepository $cartRepository, CartItemRepository $cartItemRepository)
    {
        $this->productRepository = $productRepository;
        $this->cartRepository = $cartRepository;
        $this->cartItemRepository = $cartItemRepository;
    }

    public function getItemTotal(CartItem $item): float
    {
        /** @var Product $product */
        $product = $item->getProduct();
        return $product->getPrice() * $item->getQuantity();
    }

    public function getCartTotal(Cart $cart): float
    {
        $total = 0;
        /** @var CartItem $item */
        foreach ($cart->getItems() as $item) {
            $total += $this->getItemTotal($item);
        }
        return $total;
    }

    /**
     * @param int $productId
     * @param int $cartId
     * @return CartItem
     * @throws InvalidArgumentException
     */
    public function addProductToCart(int $productId, int $cartId): CartItem
    {
        /** @var Product $product */
        $product = $this->productRepository->find($productId);
        if ($product == null) {
            throw new InvalidArgumentException("No product found with id $productId");
        }
        /** @var Cart $cart */
        $cart = $this->cartRepository->find($cartId);
        if ($cart == null) {
            throw new InvalidArgumentException("No cart found with id $cartId");
        }

        $cartItem = $this->cartItemRepository->findOneBy(['cart' => $cart, 'product' => $product]);
        if ($cartItem == null) {
            $cartItem = new CartItem();
            $cartItem->setProduct($product);
            $cartItem->setQuantity(1);
            $cart->addItem($cartItem);
        } else {
            $cartItem->setQuantity($cartItem->getQuantity() + 1);
        }

        return $cartItem;
    }
}
