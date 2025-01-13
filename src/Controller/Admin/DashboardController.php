<?php

namespace App\Controller\Admin;

use function Symfony\Component\Translation\t;
use App\Entity\Post;
use App\Entity\User;
use App\Enum\UserRole;
use App\Entity\Comment;
use App\Entity\Category;
use App\Config\AppConfig;
use App\Config\UserAvatarConfig;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Asset;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Config\UserMenu;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;

class DashboardController extends AbstractDashboardController
{
    public function __construct(private TranslatorInterface $translator) {}

    #[Route('/admin/{_locale}', name: 'admin')]
    public function index(): Response
    {
        return $this->redirectToRoute('admin_post_index');

        // Option 3. You can render some custom template to display a proper dashboard with widgets, etc.
        // (tip: it's easier if your template extends from @EasyAdmin/page/content.html.twig)
        //
        // return $this->render('some/path/my-dashboard.html.twig');
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setFaviconPath('favicon.svg')
            ->setTitle($this->getParameter('app_name'))
            ->setLocales([
                'fr' => $this->translator->trans('locale.french.label', [], 'EasyAdminBundle'),
                'en' => $this->translator->trans('locale.english.label', [], 'EasyAdminBundle')
            ])
            ->renderContentMaximized();
    }

    public function configureUserMenu(UserInterface $user): UserMenu
    {
        /** @var User $user */
        $userMenu = parent::configureUserMenu($user)
            ->addMenuItems([
                MenuItem::linkToCrud(t('my.profile', [], 'EasyAdminBundle'), 'fa fa-id-card', User::class)
                    ->setAction(Action::DETAIL)
                    ->setEntityId($user->getId()),
                MenuItem::linkToCrud(t('edit.profile', [], 'EasyAdminBundle'), 'fa fa-user-cog', User::class)
                    ->setAction(Action::EDIT)
                    ->setEntityId($user->getId()),
            ]);

        if ($user->getAvatar()) {
            $userMenu->setAvatarUrl(UserAvatarConfig::getConfig()->imgPath($user->getAvatar()));
        }

        return $userMenu;
    }

    public function configureMenuItems(): iterable
    {
        // Blog
        yield MenuItem::section(t('blog.label', [], 'EasyAdminBundle'));
        yield MenuItem::linkToCrud(t('category.label.plural', [], 'EasyAdminBundle'), 'fas fa-list', Category::class)
            ->setPermission(UserRole::SUPER_ADMIN->value);
        yield MenuItem::linkToCrud(t('post.label.plural', [], 'EasyAdminBundle'), 'fas fa-newspaper', Post::class);
        yield MenuItem::linkToCrud(t('comment.label.plural', [], 'EasyAdminBundle'), 'fas fa-comments', Comment::class);

        // Community
        yield MenuItem::section(t('community.label', [], 'EasyAdminBundle'));
        yield MenuItem::linkToCrud(t('user.label.plural', [], 'EasyAdminBundle'), 'fas fa-user', User::class)
            ->setPermission(UserRole::SUPER_ADMIN->value);
        yield MenuItem::linkToUrl(t('contribute_or_report_bug', [], 'EasyAdminBundle'), 'fa-brands fa-github', AppConfig::GITHUB_URL)
            ->setLinkTarget('_blank');

        // Website
        yield MenuItem::section(t('website.label', [], 'EasyAdminBundle'));
        yield MenuItem::linkToRoute(t('back.website.label', [], 'EasyAdminBundle'), 'fas fa-home', 'app_homepage');
    }

    public function configureAssets(): Assets
    {
        return parent::configureAssets()->addAssetMapperEntry('admin');
    }

    public function configureActions(): Actions
    {
        return parent::configureActions()
            ->update(
                Crud::PAGE_INDEX,
                Action::NEW,
                fn(Action $action) => $action->setIcon('fa fa-plus')
            )
            ->update(
                Crud::PAGE_INDEX,
                Action::EDIT,
                fn(Action $action) => $action->setIcon('fa fa-pen-to-square')
            )
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->update(
                Crud::PAGE_INDEX,
                Action::DETAIL,
                fn(Action $action) => $action->setIcon('fa fa-eye')
            )
            ->update(
                Crud::PAGE_INDEX,
                Action::DELETE,
                fn(Action $action) => $action->setIcon('fa fa-trash-can text-danger')
            )
            ->update(
                Crud::PAGE_INDEX,
                Action::BATCH_DELETE,
                fn(Action $action) => $action->setIcon('fa fa-trash-can')
            )
            ->update(
                Crud::PAGE_NEW,
                Action::SAVE_AND_ADD_ANOTHER,
                fn(Action $action) => $action->setIcon('fa fa-circle-plus')
            )
            ->update(
                Crud::PAGE_EDIT,
                Action::SAVE_AND_CONTINUE,
                fn(Action $action): Action =>
                $action->setLabel(t('save_and_continue.editing.label', [], 'EasyAdminBundle'))
                    ->setIcon('fa fa-pen-to-square')
            )
            ->update(
                Crud::PAGE_NEW,
                Action::SAVE_AND_RETURN,
                fn(Action $action): Action =>
                $action->setIcon('fa fa-save')
            )
            ->update(
                Crud::PAGE_EDIT,
                Action::SAVE_AND_RETURN,
                fn(Action $action): Action =>
                $action->setLabel(t('save.label', [], 'EasyAdminBundle'))
                    ->setIcon('fa fa-save')
            )
            ->remove(Crud::PAGE_DETAIL, Action::INDEX)
            ->update(
                Crud::PAGE_DETAIL,
                Action::EDIT,
                fn(Action $action) => $action->setIcon('fa fa-pen-to-square')
            );
    }
}
