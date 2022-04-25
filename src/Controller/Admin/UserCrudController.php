<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Service\UserRoleService;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class UserCrudController extends AbstractCrudController
{
    public function __construct(private UserPasswordHasherInterface $passwordHasher, private UserRoleService $userRoleService, private TokenStorageInterface $tokenStorage)
    {
    }

    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Utilisateur')
            ->setEntityLabelInPlural('Utilisateurs')
            ->setEntityPermission('ROLE_ADMIN')
            // ...
            ;
    }

    public function configureFields(string $pageName): iterable
    {
        $fields = [
            IdField::new('id')->onlyOnIndex(),
            TextField::new('username'),
            TextField::new('email'),
        ];

        if ($pageName === Crud::PAGE_INDEX || $pageName === Crud::PAGE_DETAIL || $this->userRoleService->sessionUserCanChangeUserRole($this->getContext()?->getEntity()->getInstance())) {
            $fields[] = ChoiceField::new('singleRole')->setChoices(
                $this->userRoleService->getAllowedNewRoleChoices()
            )->setTemplatePath('admin/user-role.html.twig');
        }

        array_push($fields, TextField::new('plainPassword')->onlyOnForms());

        return $fields;
    }

    public function configureActions(Actions $actions): Actions
    {
        // TODO Use voters to check user permissions instead of upate the display
        if($this->isGranted('ROLE_SUPER_ADMIN')){
            return $actions;
        }

        $checker = function (User $entity) {
            if ($entity === $this->getUser()) {
                return true;
            }
            return !$entity->isTeam();
        };

        $actions->update(Crud::PAGE_INDEX, Action::DELETE, function (Action $action) use ($checker){
            return $action->displayIf($checker);
        });
        $actions->update(Crud::PAGE_INDEX, Action::EDIT, function (Action $action) use ($checker){
            return $action->displayIf($checker);
        });

        return $actions;
    }

    /**
     * @param User $entityInstance
     */
    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void {
        $token = $this->tokenStorage->getToken();
        /** @var User|null $createdBy */
        $createdBy = isset($token) ? $token->getUser() : null;
        if (!$createdBy instanceof User) {
            $createdBy = null;
        }
        //$entityInstance->setCreatedBy($createdBy);

        $this->encodePassword($entityInstance);
        parent::persistEntity($entityManager, $entityInstance);
    }

    public function updateEntity(EntityManagerInterface $entityManager, $entityInstance): void {
        $this->encodePassword($entityInstance);
        parent::updateEntity($entityManager, $entityInstance);
    }

    public function delete(AdminContext $context) {
        if (!$this->userRoleService->sessionUserCanChangeUserRole($this->getContext()?->getEntity()->getInstance())) {
            throw $this->createAccessDeniedException("Cannot delete team member");
        }
        return parent::delete($context);
    }

    protected function encodePassword(User $user) {
        if ($user->getPlainPassword() !== null) {
            $encodedPassword = $this->passwordHasher->hashPassword($user, $user->getPlainPassword());
            $user->setPassword($encodedPassword);
        }
    }
}
