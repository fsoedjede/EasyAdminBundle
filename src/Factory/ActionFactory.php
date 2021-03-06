<?php

namespace EasyCorp\Bundle\EasyAdminBundle\Factory;

use EasyCorp\Bundle\EasyAdminBundle\Collection\ActionCollection;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Dto\ActionConfigDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\ActionDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Provider\AdminContextProvider;
use EasyCorp\Bundle\EasyAdminBundle\Router\CrudUrlGenerator;
use EasyCorp\Bundle\EasyAdminBundle\Security\Permission;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @author Javier Eguiluz <javier.eguiluz@gmail.com>
 */
final class ActionFactory
{
    private $adminContextProvider;
    private $authChecker;
    private $translator;
    private $urlGenerator;
    private $crudUrlGenerator;

    public function __construct(AdminContextProvider $adminContextProvider, AuthorizationCheckerInterface $authChecker, TranslatorInterface $translator, UrlGeneratorInterface $urlGenerator, CrudUrlGenerator $crudUrlGenerator)
    {
        $this->adminContextProvider = $adminContextProvider;
        $this->authChecker = $authChecker;
        $this->translator = $translator;
        $this->urlGenerator = $urlGenerator;
        $this->crudUrlGenerator = $crudUrlGenerator;
    }

    public function processEntityActions(EntityDto $entityDto, ActionConfigDto $actionsDto): void
    {
        $currentPage = $this->adminContextProvider->getContext()->getCrud()->getCurrentPage();
        $entityActions = [];
        foreach ($actionsDto->getActions()->all() as $actionDto) {
            if (!$actionDto->isEntityAction()) {
                continue;
            }

            if (false === $this->authChecker->isGranted(Permission::EA_EXECUTE_ACTION, $actionDto)) {
                continue;
            }

            if (false === $actionDto->shouldBeDisplayedFor($entityDto)) {
                continue;
            }

            $entityActions[] = $this->processAction($currentPage, $actionDto, $entityDto);
        }

        $entityDto->setActions(ActionCollection::new($entityActions));
    }

    public function processGlobalActions(ActionConfigDto $actionsDto): ActionCollection
    {
        $currentPage = $this->adminContextProvider->getContext()->getCrud()->getCurrentPage();
        $globalActions = [];
        foreach ($actionsDto->getActions()->all() as $actionDto) {
            if (!$actionDto->isGlobalAction()) {
                continue;
            }

            // TODO: remove this when we reenable "batch actions"
            if ($actionDto->isBatchAction()) {
                throw new \RuntimeException(sprintf('Batch actions are not supported yet, but we\'ll add support for them very soon. Meanwhile, remove the "%s" batch action from the "%s" page.', $actionDto->getName(), $currentPage));
            }

            if (false === $this->authChecker->isGranted(Permission::EA_EXECUTE_ACTION, $actionDto)) {
                continue;
            }

            if (Crud::PAGE_INDEX !== $currentPage && $actionDto->isBatchAction()) {
                throw new \RuntimeException(sprintf('Batch actions can be added only to the "index" page, but the "%s" batch action is defined in the "%s" page.', $actionDto->getName(), $currentPage));
            }

            $globalActions[] = $this->processAction($currentPage, $actionDto);
        }

        return ActionCollection::new($globalActions);
    }

    private function processAction(string $pageName, ActionDto $actionDto, ?EntityDto $entityDto = null): ActionDto
    {
        $adminContext = $this->adminContextProvider->getContext();
        $defaultTranslationDomain = $adminContext->getI18n()->getTranslationDomain();
        $defaultTranslationParameters = $adminContext->getI18n()->getTranslationParameters();
        $currentPage = $adminContext->getCrud()->getCurrentPage();

        if (false === $actionDto->getLabel()) {
            $actionDto->setHtmlAttributes(array_merge(['title' => $actionDto->getName()], $actionDto->getHtmlAttributes()));
        } else {
            $translationParameters = array_merge($defaultTranslationParameters, $actionDto->getTranslationParameters());
            $translatedActionLabel = $this->translator->trans($actionDto->getLabel(), $translationParameters, $actionDto->getTranslationDomain() ?? $defaultTranslationDomain);
            $actionDto->setLabel($translatedActionLabel);
        }

        $defaultTemplatePath = $adminContext->getTemplatePath('crud/action');
        $actionDto->setTemplatePath($actionDto->getTemplatePath() ?? $defaultTemplatePath);

        $actionDto->setLinkUrl($this->generateActionUrl($currentPage, $adminContext->getRequest(), $actionDto, $entityDto));

        if (!$actionDto->isGlobalAction() && \in_array($pageName, [Crud::PAGE_EDIT, Crud::PAGE_NEW], true)) {
            $actionDto->setHtmlAttribute('form', sprintf('%s-%s-form', $pageName, $entityDto->getName()));
        }

        return $actionDto;
    }

    private function generateActionUrl(string $currentAction, Request $request, ActionDto $actionDto, ?EntityDto $entityDto = null): string
    {
        if (null !== $routeName = $actionDto->getRouteName()) {
            $routeParameters = $actionDto->getRouteParameters();
            if (\is_callable($routeParameters)) {
                $routeParameters = $routeParameters($entityDto->getInstance());
            }

            return $this->urlGenerator->generate($routeName, $routeParameters);
        }

        $requestParameters = [
            'crudController' => $request->query->get('crudController'),
            'crudAction' => $actionDto->getCrudActionName(),
            'referrer' => $this->generateReferrerUrl($request, $actionDto, $currentAction),
        ];

        if (null !== $entityDto && !\in_array($actionDto->getName(), [Action::INDEX, Action::NEW], true)) {
            $requestParameters['entityId'] = $entityDto->getPrimaryKeyValueAsString();
        }

        return $this->crudUrlGenerator->build()->setQueryParameters($requestParameters)->generateUrl();
    }

    private function generateReferrerUrl(Request $request, ActionDto $actionDto, string $currentAction): ?string
    {
        $nextAction = $actionDto->getName();

        if (Action::DETAIL === $currentAction) {
            if (Action::EDIT === $nextAction) {
                return $this->crudUrlGenerator->build()->removeReferrer()->generateUrl();
            }
        }

        if (Action::INDEX === $currentAction) {
            return $this->crudUrlGenerator->build()->removeReferrer()->generateUrl();
        }

        if (Action::NEW === $currentAction) {
            return null;
        }

        $referrer = $request->get('referrer');
        $referrerParts = parse_url($referrer);
        parse_str($referrerParts['query'] ?? '', $referrerQueryStringVariables);
        $referrerCrudAction = $referrerQueryStringVariables['crudAction'] ?? null;

        if (Action::EDIT === $currentAction) {
            if (\in_array($referrerCrudAction, [Action::INDEX, Action::DETAIL], true)) {
                return $referrer;
            }
        }

        return $this->crudUrlGenerator->build()->removeReferrer()->generateUrl();
    }
}
