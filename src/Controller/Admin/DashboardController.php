<?php

namespace App\Controller\Admin;

use App\Entity\Post;
use App\Entity\User;
use App\Entity\Category;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use Symfony\Component\Security\Core\User\UserInterface;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use EasyCorp\Bundle\EasyAdminBundle\Config\UserMenu;

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
            ->setTitle('Techinsiders')
            ->setLocales([
                'fr' => '🇫🇷 Français', 
                'en' => '🇬🇧 English'
            ])
            ->renderContentMaximized();
    }

    public function configureUserMenu(UserInterface $user): UserMenu
    {
        /** @var User $user */
        $userMenu = parent::configureUserMenu($user)
            ->addMenuItems([
                MenuItem::linkToCrud('My Profile', 'fa fa-id-card', User::class)
                    ->setAction('detail')
                    ->setEntityId($user->getId()),
                MenuItem::linkToCrud('Settings', 'fa fa-user-cog', User::class)
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
        yield MenuItem::section('Site Web');
        yield MenuItem::linktoRoute('Retour sur le site', 'fas fa-home', 'app_home');
        
        yield MenuItem::section('Communauté')
            ->setPermission('ROLE_SUPER_ADMIN');
        yield MenuItem::linkToCrud('Utilisateurs', 'fas fa-user', User::class)
            ->setPermission('ROLE_SUPER_ADMIN');
            
        yield MenuItem::section('Blog');
        yield MenuItem::linkToCrud('Catégories', 'fas fa-list', Category::class)
            ->setPermission('ROLE_ADMIN');
        yield MenuItem::linkToCrud('Articles', 'fas fa-newspaper', Post::class);
    }
}
