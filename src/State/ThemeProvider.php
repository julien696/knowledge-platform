<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Entity\Theme;
use App\Repository\ThemeRepository;
use Symfony\Component\HttpFoundation\RequestStack;

class ThemeProvider implements ProviderInterface
{
    public function __construct(
        private ThemeRepository $themeRepository,
        private RequestStack $requestStack
    ) {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        $request = $this->requestStack->getCurrentRequest();
        $path = $request->getPathInfo();

        if (str_contains($path, '/themes/') && $request->getMethod() === 'GET') {
            $themeId = (int) $uriVariables['id'];
            return $this->themeRepository->findWithCursusAndLessons($themeId);
        }

        return null;
    }
}
