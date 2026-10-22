<?php

namespace App\Controller\Front;

use App\Repository\BrandRepository;
use App\Repository\CategoryRepository;
use App\Repository\ProductRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(
        Request $request,
        ProductRepository $productRepository,
        CategoryRepository $categoryRepository,
        BrandRepository $brandRepository
    ): Response {
        $search = trim($request->query->getString('q'));
        $search = $search === '' ? null : mb_substr($search, 0, 100);

        $categoryId = $this->getPositiveIntegerFilter($request, 'category');
        $brandId = $this->getPositiveIntegerFilter($request, 'brand');
        $minimumPrice = $this->getNonNegativeIntegerFilter($request, 'min_price');
        $maximumPrice = $this->getNonNegativeIntegerFilter($request, 'max_price');

        return $this->render('Front/home/index.html.twig', [
            'products' => $productRepository->findPublished(
                $search,
                $categoryId,
                $brandId,
                $minimumPrice,
                $maximumPrice
            ),
            'categories' => $categoryRepository->findBy([], ['label' => 'ASC']),
            'brands' => $brandRepository->findBy([], ['label' => 'ASC']),
            'filters' => [
                'q' => $search ?? '',
                'category' => $categoryId,
                'brand' => $brandId,
                'min_price' => $minimumPrice,
                'max_price' => $maximumPrice,
            ],
        ]);
    }

    private function getPositiveIntegerFilter(Request $request, string $name): ?int
    {
        $value = $request->query->getString($name);
        if ($value === '' || !ctype_digit($value) || (int) $value < 1) {
            return null;
        }

        return (int) $value;
    }

    private function getNonNegativeIntegerFilter(Request $request, string $name): ?int
    {
        $value = $request->query->getString($name);
        if ($value === '' || !ctype_digit($value)) {
            return null;
        }

        return (int) $value;
    }
}
