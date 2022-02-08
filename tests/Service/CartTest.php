<?php

namespace App\Tests\Service;

use App\Entity\Cart;
use App\Entity\CartItem;
use App\Entity\Product;
use App\Repository\CartItemRepository;
use App\Repository\CartRepository;
use App\Repository\ProductRepository;
use App\Service\CartManager;
use InvalidArgumentException;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class CartTest extends KernelTestCase
{
    public function testItemTotal(): void
    {
        self::bootKernel();

        $container = static::getContainer();

        /** @var CartManager $cartManager */
        $cartManager = $container->get(CartManager::class);

        $product = new Product();
        $product->setPrice(0);
        $item = new CartItem();
        $item->setProduct($product);
        $item->setQuantity(0);
        $this->assertEquals(0, $cartManager->getItemTotal($item));

        $product = new Product();
        $product->setPrice(1);
        $item = new CartItem();
        $item->setProduct($product);
        $item->setQuantity(1);
        $this->assertEquals(1, $cartManager->getItemTotal($item));

        $product = new Product();
        $product->setPrice(0.50);
        $item = new CartItem();
        $item->setProduct($product);
        $item->setQuantity(4);
        $this->assertEquals(2, $cartManager->getItemTotal($item));
    }

    public function testCartTotal(): void
    {
        self::bootKernel();

        $container = static::getContainer();

        /** @var CartManager $cartManager */
        $cartManager = $container->get(CartManager::class);

        $this->assertEquals(0, $cartManager->getCartTotal(new Cart()));

        $product = new Product();
        $product->setPrice(1);
        $item = new CartItem();
        $item->setProduct($product);
        $item->setQuantity(1);
        $cart = new Cart();
        $cart->addItem($item);
        $this->assertEquals(1, $cartManager->getCartTotal($cart));

        $product = new Product();
        $product->setPrice(0.50);
        $item = new CartItem();
        $item->setProduct($product);
        $item->setQuantity(4);
        $cart->addItem($item);
        $this->assertEquals(3, $cartManager->getCartTotal($cart));
    }

    public function testAddProductToCartTotal(): void
    {
        $cart = new Cart();

        $product = new Product();
        $product->setPrice(1);
        $item = new CartItem();
        $item->setProduct($product);
        $item->setQuantity(1);
        $cart->addItem($item);

        $product = new Product();
        $product->setPrice(0.50);
        $item = new CartItem();
        $item->setProduct($product);
        $item->setQuantity(4);
        $cart->addItem($item);
        $cartRepository = $this->createMock(CartRepository::class);
        $cartRepository->expects($this->any())
            ->method('find')
            ->willReturn($cart);

        $product = new Product();
        $product->setPrice(2);
        $productRepository = $this->createMock(ProductRepository::class);
        $productRepository->expects($this->any())
            ->method('find')
            ->willReturn($product);

        $cartItemRepository = $this->createMock(CartItemRepository::class);
        $cartItemRepository->expects($this->any())
            ->method('findOneBy')
            ->willReturn(null);

        $cartManager = new CartManager($productRepository, $cartRepository, $cartItemRepository);
        $cartItem = $cartManager->addProductToCart(1, 1);

        $this->assertEquals(1, $cartItem->getQuantity());

        $item = new CartItem();
        $item->setProduct($product);
        $item->setQuantity(1);

        $cartItemRepository = $this->createMock(CartItemRepository::class);
        $cartItemRepository->expects($this->any())
            ->method('findOneBy')
            ->willReturn($item);

        $cartManager = new CartManager($productRepository, $cartRepository, $cartItemRepository);
        $cartItem = $cartManager->addProductToCart(1, 1);

        $this->assertEquals(2, $cartItem->getQuantity());
    }

    public function testAddNullProductToCart()
    {
        $productRepository = $this->createMock(ProductRepository::class);
        $productRepository->expects($this->any())
            ->method('find')
            ->willReturn(null);

        $cartRepository = $this->createMock(CartRepository::class);
        $cartRepository->expects($this->any())
            ->method('find')
            ->willReturn(new Cart());

        $cartItemRepository = $this->createMock(CartItemRepository::class);
        $cartItemRepository->expects($this->any())
            ->method('findOneBy')
            ->willReturn(new CartItem());

        $cartManager = new CartManager($productRepository, $cartRepository, $cartItemRepository);
        $this->expectException(InvalidArgumentException::class);
        $cartManager->addProductToCart(1, 1);
    }

    public function testAddProductToNullCart()
    {
        $productRepository = $this->createMock(ProductRepository::class);
        $productRepository->expects($this->any())
            ->method('find')
            ->willReturn(new Product());

        $cartRepository = $this->createMock(CartRepository::class);
        $cartRepository->expects($this->any())
            ->method('find')
            ->willReturn(null);

        $cartItemRepository = $this->createMock(CartItemRepository::class);
        $cartItemRepository->expects($this->any())
            ->method('findOneBy')
            ->willReturn(new CartItem());

        $cartManager = new CartManager($productRepository, $cartRepository, $cartItemRepository);
        $this->expectException(InvalidArgumentException::class);
        $cartManager->addProductToCart(1, 1);
    }
}
