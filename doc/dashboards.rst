Dashboards
==========

.. raw:: html

    <div class="box box--small box--warning">
        <strong class="title">WARNING:</strong>

        You are browsing the documentation for <strong>EasyAdmin 3.x</strong>,
        which hasn't been released as a stable version yet. You are probably
        using EasyAdmin 2.x in your application, so you can switch to
        <a href="https://symfony.com/doc/2.x/bundles/EasyAdminBundle/index.html">EasyAdmin 2.x docs</a>.
    </div>

**Dashboards** are the entry point of backends and they link to one ore more
:doc:`resources </crud>`. Dashboards also display a main menu to navigate the
resources and the information of the logged in user.

Imagine that you have a simple application with three Doctrine entities: users,
blog posts and categories. Your own employees can create and edit any of them
but external collaborators can only create blog posts.

You can implement this in EasyAdmin as follows:

* Create three CRUD controllers (e.g. ``UserCrudController``, ``BlogPostCrudController``
  and ``CategoryCrudController``);
* Create a dashboard for your employees (e.g. ``DashboardController``) and link
  to the three resources;
* Create a dashboard for your external collaborators (e.g. ``ExternalDashboardController``)
  and link only to the ``BlogPostCrudController`` resource.

Technically, dashboards are regular `Symfony controllers`_ so you can do
anything you usually do in a controller, such as injecting services and using
shortcuts like ``$this->render()`` or ``$this->isGranted()``.

Dashboard classes must implement the
``EasyCorp\Bundle\EasyAdminBundle\Contracts\Controller\DashboardInterface``,
which ensures that certain methods are defined in the dashboard. Instead of
implementing the interface, you can also extend from the
``AbstractDashboardController`` class. Run the following command to quickly
generate a dashboard controller:

.. code-block:: terminal

    $ php bin/console make:admin:dashboard

Dashboard Configuration
-----------------------

The basic dashboard configuration is defined in the ``configureDashboard()``
method (the main menu and the user menu are configured in their own methods, as
explained later)::

    use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
    use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;

    class DashboardController extends AbstractDashboardController
    {
        // ...

        public function configureDashboard(): Dashboard
        {
            return Dashboard::new()
                // the name visible to end users
                ->setTitle('ACME Corp.')
                // you can include HTML contents too (e.g. to link to an image)
                ->setTitle('<img src="..."> ACME <span class="text-small>Corp.</span>')
                // the domain used by default is 'messages'
                ->setTranslationDomain('my-custom-domain')
                // etc.
            ;
        }
    }

.. _dashboard-menu:

Main Menu
---------

The **main menu** links to different :doc:`CRUD controllers </crud>` from the
dashboard. It's the only way to associate dashboards and resources. For security
reasons, a backend can only access to the resources associated to the dashboard
via the main menu.

The main menu is a collection of objects implementing
``EasyCorp\Bundle\EasyAdminBundle\Contracts\Menu\MenuInterface`` that configure
the look and behavior of each menu item::

    use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
    use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
    use App\Entity\Category;
    use App\Entity\BlogPost;
    use App\Entity\Comment;
    use App\Entity\User;

    class DashboardController extends AbstractDashboardController
    {
        // ...

        public function configureMenuItems(): iterable
        {
            return [
                MenuItem::linkToDashboard('Dashboard', 'fa-home'),

                MenuItem::section('Blog'),
                MenuItem::linkToCrud('Categories', 'fa fa-tags', Category::class),
                MenuItem::linkToCrud('Blog Posts', 'fa fa-file-text', BlogPost::class),

                MenuItem::section('Users'),
                MenuItem::linkToCrud('Comments', 'fa fa-comment', Comment::class),
                MenuItem::linkToCrud('Users', 'fa fa-user', User::class),
            ];
        }
    }

The first argument of ``MenuItem::new()`` is the label displayed by the item and
the second argument is the full CSS class of the `FontAwesome`_ icon to display.

Menu Item Configuration Options
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

All menu items define the following methods to configure some options:

* ``setCssClass(string $cssClass)``, sets the CSS class or classes applied to
  the ``<li>`` parent element of the menu item;
* ``setLinkRel(string $rel)``, sets the ``rel`` HTML attribute of the menu item
  link (check out the `allowed values for the "rel" attribute`_);
* ``setLinkTarget(string $target)``, sets the ``target`` HTML attribute of the
  menu item link (``_self`` by default);
* ``setPermission(string $permission)``, sets the `Symfony security permission`_
  that the user must have to see this menu item. Read the :ref:`menu security reference <security-menu>`
  for more details.

The rest of options depend on each menu item type, as explained in the next sections.

Menu Item Types
~~~~~~~~~~~~~~~

CRUD Menu Item
..............

This is the most common menu item type and it links to some action of some
:doc:`CRUD controller </crud>`. Instead of passing the FQCN *(fully-qualified
class name)* of the CRUD controller, you must pass the FQCN of the Doctrine
entity associated to the CRUD controller::

    use App\Entity\Category;
    use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;

    public function configureMenuItems(): iterable
    {
        return [
            // ...

            // links to the 'index' action of the Category CRUD controller
            MenuItem::linkToCrud('Categories', 'fa fa-tags', Category::class),

            // links to a different CRUD action
            MenuItem::linkToCrud('Add Category', 'fa fa-tags', Category::class)
                ->setAction('new'),

            MenuItem::linkToCrud('Show Main Category', 'fa fa-tags', Category::class)
                ->setAction('detail')
                ->setEntityId(1),

            // if the same Doctrine entity is associated to more than one CRUD controller,
            // use the 'setController()' method to specify which controller to use
            MenuItem::linkToCrud('Categories', 'fa fa-tags', Category::class)
                ->setController('LegacyCategoryCrudController::class'),

            // uses custom sorting options for the listing
            MenuItem::linkToCrud('Categories', 'fa fa-tags', Category::class)
                ->setQueryParameter('sortField', 'createdAt'),
                ->setQueryParameter('sortDirection', 'DESC'),
        ];
    }

Dashboard Menu Item
...................

It links to the homepage of the current dashboard. You can achieve the same with
a "route menu item" (explained below) but this one is simpler because you don't
have to specify the route name (it's found automatically)::

    use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;

    public function configureMenuItems(): iterable
    {
        return [
            MenuItem::linkToDashboard('Home', 'fa fa-home'),
            // ...
        ];
    }

Route Menu Item
...............

It links to any of the Symfony application routes::

    use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;

    public function configureMenuItems(): iterable
    {
        return [
            MenuItem::linkToRoute('The Label', 'fa ...', 'route_name'),
            MenuItem::linkToRoute('The Label', 'fa ...', 'route_name', [ ... route parameters ... ]),
            // ...
        ];
    }

URL Menu Item
.............

It links to a relative or absolute URL::

    use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;

    public function configureMenuItems(): iterable
    {
        return [
            MenuItem::linkToUrl('Visit public website', null, '/'),
            MenuItem::linkToUrl('Search in Google', 'fab fa-google', 'https://google.com'),
            // ...
        ];
    }

To avoid leaking internal backend information to external websites, if the menu
item links to an external URL and doesn't define its ``rel`` option, the
``rel="noreferrer"`` attribute is added automatically.

Section Menu Item
.................

It creates a visual separation between menu items and can optionally display a
label which acts as the title of the menu items below::

    use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;

    public function configureMenuItems(): iterable
    {
        return [
            // ...

            MenuItem::section(),
            // ...

            MenuItem::section('Blog'),
            // ...
        ];
    }

Logout Menu Item
................

It links to the URL that the user must visit to log out from the application.
If you know the logout route name, you can achieve the same with the
"route menu item", but this one is more convenient because it finds the logout
URL for the current security firewall automatically::

    use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;

    public function configureMenuItems(): iterable
    {
        return [
            // ...
            MenuItem::linkToLogout('Logout', 'fa fa-exit'),
        ];
    }

Exit Impersonation Menu Item
............................

It links to the URL that the user must visit to stop impersonating other users::

    use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;

    public function configureMenuItems(): iterable
    {
        return [
            // ...
            MenuItem::linkToExitImpersonation('Stop impersonation', 'fa fa-exit'),
        ];
    }

Submenus
~~~~~~~~

The main menu can display up to two level nested menus. Submenus are defined
using the ``subMenu()`` item type::

    use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;

    public function configureMenuItems(): iterable
    {
        return [
            MenuItem::subMenu('Blog', 'fa fa-article')->setSubItems([
                MenuItem::linkToCrud('Categories', 'fa fa-tags', Category::class),
                MenuItem::linkToCrud('Posts', 'fa fa-file-text', BlogPost::class),
                MenuItem::linkToCrud('Comments', 'fa fa-comment', Comment::class),
            ]),
            // ...
        ];
    }

.. note::

    In a submenu, the parent menu item cannot link to any resource, route or URL;
    it can only expand/collapse the submenu items.

Complex Main Menus
~~~~~~~~~~~~~~~~~~

The return type of the ``configureMenuItems()`` is ``iterable``, so you don't have
to always return an array. For example, if your main menu requires complex logic
to decide which items to display for each user, it's more convenient to use a
generator to return the menu items::

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('Dashboard', 'fa fa-home');

        if ('... some complex expression ...') {
            yield MenuItem::section('Blog');
            yield MenuItem::linkToCrud('Categories', 'fa fa-tags', Category::class);
            yield MenuItem::linkToCrud('Blog Posts', 'fa fa-file-text, BlogPost::class);
        }

        // ...
    }

.. _dashboards-user-menu:

User Menu
---------

When accessing a protected backend, EasyAdmin displays the details of the user
who is logged in the application and a menu with some options like "logout" (if
Symfony's `logout feature`_ is enabled).

The user name is the result of calling to the ``__toString()`` method on the
current user object. The user avatar is a generic avatar icon. Use the
``configureUserMenu()`` method to configure the features and items of this menu::

    use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
    use EasyCorp\Bundle\EasyAdminBundle\Config\UserMenu;
    use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
    use Symfony\Component\Security\Core\User\UserInterface;

    class DashboardController extends AbstractDashboardController
    {
        // ...

        public function configureUserMenu(UserInterface $user): UserMenu
        {
            return UserMenu::new()
                // use the given $user object to get the user name
                ->setName($user->getFullName())
                // use this method if you don't want to display the name of the user
                ->displayUserName(false)

                // you can return an URL with the avatar image
                ->setAvatarUrl('https://...')
                ->setAvatarUrl($user->getProfileImageUrl())
                // use this method if you don't want to display the user image
                ->displayUserAvatar(false)
                // you can also pass an email address to use gravatar's service
                ->setGravatarEmail($user->getMainEmailAddress())

                // you can use any type of menu item, except submenus
                ->addMenuItems([
                    MenuItem::linkToRoute('My Profile', 'fa fa-id-card', '...', ['...' => '...']),
                    MenuItem::linkToRoute('Settings', 'fa fa-user-cog', '...', ['...' => '...']),
                    MenuItem::section(),
                    MenuItem::linkToLogout('Logout', 'fa fa-sign-out'),
                ]);
        }
    }

.. _admin-context:

Admin Context
-------------

EasyAdmin initializes a variable of type ``EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext``
automatically on each backend request. This object implements the `context object`_
design pattern and stores all the information commonly needed in different parts
of the backend.

This context object is automatically injected in every template as a variable
called ``ea`` (the initials of "EasyAdmin"):

.. code-block:: twig

    <h1>{{ ea.dashboardTitle }}</h1>

    {% for menuItem in ea.mainMenu.items %}
        {# ... #}
    {% endif %}

The ``AdminContext`` variable is created dynamically on each request, so you
can't inject it directly in your services. Instead, use the ``AdminContextProvider``
service to get the context variable::

    use EasyCorp\Bundle\EasyAdminBundle\Provider\AdminContextProvider;

    final class SomeService
    {
        private $adminContextProvider;

        public function __construct(AdminContextProvider $adminContextProvider)
        {
            $this->adminContextProvider = $adminContextProvider;
        }

        public function someMethod()
        {
            $context = $this->adminContextProvider->getContext();
        }

        // ...
    }

In controllers, use the ``AdminContext`` type-hint in any argument where you
want to inject the context object::

    use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
    use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

    class SomeController extends AbstractController
    {
        public function someMethod(AdminContext $context)
        {
            // ...
        }
    }

Translation
-----------

The backend interface is fully translated using the `Symfony translation`_
features. EasyAdmin own messages and contents use the ``EasyAdminBundle``
translation domain (thanks to our community for kindly providing translations
for tens of languages).

The rest of the contents (e.g. the label of the menu items, entity and field
names, etc.) use the ``messages`` translation domain by default. You can change
this value with the ``translationDomain()`` method::

    class DashboardController extends AbstractDashboardController
    {
        // ...

        public function configureDashboard(): Dashboard
        {
            return Dashboard::new()
                // ...

                // the argument is the name of any valid Symfony translation domain
                ->setTranslationDomain('admin');
        }
    }

The backend uses the same language configured in the Symfony application.
When the locale is Arabic (``ar``), Persian (``fa``) or Hebrew (``he``), the
HTML text direction is set to ``rtl`` (right-to-left) automatically. Otherwise,
the text is displayed as ``ltr`` (left-to-right), but you can configure this
value explicitly::

    class DashboardController extends AbstractDashboardController
    {
        // ...

        public function configureDashboard(): Dashboard
        {
            return Dashboard::new()
                // ...

                // most of the times there's no need to configure this explicitly
                // (default: 'rtl' or 'ltr' depending on the language)
                ->setTextDirection('rtl');
        }
    }

.. tip::

    If you want to make the backend use a different language than the public
    website, you'll need to `work with the user locale`_ to set the request
    locale before the translation service retrieves it.

.. note::

    The contents stored in the database (e.g. the content of a blog post or the
    name of a product) are not translated. EasyAdmin does not support the
    translation of the entity property contents into different languages.

Page Templates
--------------

EasyAdmin provides several page templates which are useful when adding custom
logic in your dashboards.

Login Form Template
~~~~~~~~~~~~~~~~~~~

Twig Template Path: ``@EasyAdmin/page/login.html.twig``

It displays a simple username + password login form that matches the style of
the rest of the backend. The template defines lots of config options, but most
applications can rely on its default values:

.. code-block:: php

    namespace App\Controller;

    use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
    use Symfony\Component\HttpFoundation\Response;
    use Symfony\Component\Routing\Annotation\Route;
    use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

    class SecurityController extends AbstractControllerController
    {
        /**
         * @Route("/login", name="login")
         */
        public function login(AuthenticationUtils $authenticationUtils): Response
        {
            $error = $authenticationUtils->getLastAuthenticationError();
            $lastUsername = $authenticationUtils->getLastUsername();

            return $this->render('@EasyAdmin/page/login.html.twig', [
                // parameters usually defined in Symfony login forms
                'error' => $error,
                'last_username' => $lastUsername,

                // OPTIONAL parameters to customize the login form:

                // the string used to generate the CSRF token. If you don't define
                // this parameter, the login form won't include a CSRF token
                'csrf_token_intention' => 'authenticate',
                // the URL users are redirected to after the login (default: '/admin')
                'target_path' => $this->generateUrl('admin_dashboard'),
                // the label displayed for the username form field (the |trans filter is applied to it)
                'username_label' => 'Your username',
                // the label displayed for the password form field (the |trans filter is applied to it)
                'password_label' => 'Your password',
                // the label displayed for the Sign In form button (the |trans filter is applied to it)
                'sign_in_label' => 'Log in',
                // the 'name' HTML attribute of the <input> used for the username field (default: '_username')
                'username_parameter' => 'my_custom_username_field',
                // the 'name' HTML attribute of the <input> used for the password field (default: '_password')
                'password_parameter' => 'my_custom_password_field',
            ]);
        }
    }

Content Page Template
~~~~~~~~~~~~~~~~~~~~~

Twig Template Path: ``@EasyAdmin/page/content.html.twig``

It displays a simple page similar to the index/detail/form pages, with the main
header, the sidebar menu and the central content section. The only difference is
that the content section is completely empty, so it's useful to display your own
text contents, custom forms, etc.

Blank Page Template
~~~~~~~~~~~~~~~~~~~

Twig Template Path: ``@EasyAdmin/page/blank.html.twig``

It displays a page with the same header and sidebar menu as the
index/detail/form pages, but without the central content section. It's useful to
define completely custom page, such as a complex dashboard.

.. _`Symfony controllers`: https://symfony.com/doc/current/controller.html
.. _`context object`: https://wiki.c2.com/?ContextObject
.. _`FontAwesome`: https://fontawesome.com/
.. _`allowed values for the "rel" attribute`: https://developer.mozilla.org/en-US/docs/Web/HTML/Link_types
.. _`Symfony security permission`: https://symfony.com/doc/current/security.html#roles
.. _`logout feature`: https://symfony.com/doc/current/security.html#logging-out
.. _`Symfony translation`: https://symfony.com/doc/current/components/translation.html
.. _`translation domain`: https://symfony.com/doc/current/components/translation.html#using-message-domains
.. _`translation domains`: https://symfony.com/doc/current/components/translation.html#using-message-domains
.. _`work with the user locale`: https://symfony.com/doc/current/translation/locale.html
