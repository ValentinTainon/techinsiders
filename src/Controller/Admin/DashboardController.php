<?php

namespace App\Controller\Admin;

use App\Entity\Post;
use App\Entity\User;
use App\Entity\Category;
use function Symfony\Component\Translation\t;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Config\UserMenu;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use Symfony\Component\Security\Core\User\UserInterface;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;

class DashboardController extends AbstractDashboardController
{
    #[Route('/admin/{_locale}', name: 'admin')]
    public function index(): Response
    {
        $adminUrlGenerator = $this->container->get(AdminUrlGenerator::class);
        return $this->redirect($adminUrlGenerator->setController(PostCrudController::class)->generateUrl());

        // Option 1. You can make your dashboard redirect to some common page of your backend
        //
        // $adminUrlGenerator = $this->container->get(AdminUrlGenerator::class);
        // return $this->redirect($adminUrlGenerator->setController(OneOfYourCrudController::class)->generateUrl());

        // Option 2. You can make your dashboard redirect to different pages depending on the user
        //
        // if ('jane' === $this->getUser()->getUsername()) {
        //     return $this->redirect('...');
        // }

        // Option 3. You can render some custom template to display a proper dashboard with widgets, etc.
        // (tip: it's easier if your template extends from @EasyAdmin/page/content.html.twig)
        //
        // return $this->render('some/path/my-dashboard.html.twig');
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            // ->setFaviconPath('favicon.svg')
            ->setTitle($this->getParameter('app_name'))
            ->setLocales([
                'fr' => 'Français', 
                'en' => 'English'
            ])
            ->renderContentMaximized();
    }

    public function configureUserMenu(UserInterface $user): UserMenu
    {
        /** @var User $user */
        $userMenu = parent::configureUserMenu($user)
            ->addMenuItems([
                MenuItem::linkToCrud(t('my.profile', [], 'EasyAdminBundle'), 'fa fa-id-card', User::class)
                    ->setAction('detail')
                    ->setEntityId($user->getId()),
                MenuItem::linkToCrud(t('edit.profile', [], 'EasyAdminBundle'), 'fa fa-user-cog', User::class)
                    ->setAction('edit')
                    ->setEntityId($user->getId()),
            ]);
        
        if ($user->getAvatar()) {
            $userMenu->setAvatarUrl('/uploads/users/avatars/'.$user->getAvatar());
        }
        
        return $userMenu;
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::section(t('blog.label', [], 'EasyAdminBundle'));
        yield MenuItem::linkToCrud(t('category.label.plural', [], 'EasyAdminBundle'), 'fas fa-list', Category::class)
            ->setPermission('ROLE_SUPER_ADMIN');
        yield MenuItem::linkToCrud(t('post.label.plural', [], 'EasyAdminBundle'), 'fas fa-newspaper', Post::class);
        
        yield MenuItem::section(t('community.label', [], 'EasyAdminBundle'))
            ->setPermission('ROLE_SUPER_ADMIN');
        yield MenuItem::linkToCrud(t('user.label.plural', [], 'EasyAdminBundle'), 'fas fa-user', User::class)
            ->setPermission('ROLE_SUPER_ADMIN');
        
        yield MenuItem::section(t('website.label', [], 'EasyAdminBundle'));
        yield MenuItem::linktoRoute(t('back.website.label', [], 'EasyAdminBundle'), 'fas fa-home', 'app_home');
    }
}
