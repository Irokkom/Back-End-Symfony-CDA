<?php

// Déclaration du namespace. Cela permet de définir à quel "espace" ou "dossier" cette classe appartient
// Ici, la classe SecurityController appartient à l'espace "App\Controller"
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController; // On importe la classe de base AbstractController de Symfony, qui permet d'utiliser les méthodes de base pour un contrôleur
use Symfony\Component\HttpFoundation\Response; // On importe la classe Response, qui est utilisée pour envoyer des réponses HTTP
use Symfony\Component\Routing\Annotation\Route; // On importe l'annotation Route pour définir les routes de ce contrôleur
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils; // On importe AuthenticationUtils pour gérer l'authentification et récupérer des informations sur l'utilisateur connecté (erreurs de connexion, dernier nom d'utilisateur)

class SecurityController extends AbstractController // Déclaration de la classe SecurityController qui étend AbstractController, ce qui permet d'hériter de nombreuses méthodes utiles pour le contrôleur
{
    #[Route('/login', name: 'app_login')] // Annotation définissant la route '/login' pour afficher le formulaire de connexion. Elle associe cette route à la méthode 'login' de ce contrôleur
    public function login(AuthenticationUtils $authenticationUtils): Response // La méthode 'login' gère la connexion des utilisateurs en affichant le formulaire et en récupérant les erreurs de connexion
    {
        // Si l'utilisateur est déjà connecté, le rediriger
        if ($this->getUser()) {
            if ($this->isGranted('ROLE_ADMIN')) {
                return $this->redirectToRoute('admin_dashboard');
            }
            return $this->redirectToRoute('app_home');
        }

        $error = $authenticationUtils->getLastAuthenticationError(); // Récupère l'erreur de dernière authentification, s'il y en a une (par exemple, mot de passe incorrect)
        $lastUsername = $authenticationUtils->getLastUsername(); // Récupère le dernier nom d'utilisateur utilisé lors de la tentative de connexion (en cas d'erreur, le nom d'utilisateur est déjà pré-rempli dans le formulaire)

        // Rend la vue 'security/login.html.twig' et passe les variables nécessaires (dernier nom d'utilisateur et erreur de connexion) à la vue pour l'affichage
        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername, // Transfert du dernier nom d'utilisateur à la vue pour l'affichage
            'error' => $error, // Transfert de l'erreur de connexion à la vue pour l'affichage
        ]);
    }

    #[Route('/logout', name: 'app_logout')] // Annotation définissant la route '/logout' pour gérer la déconnexion des utilisateurs
    public function logout(): void // Méthode vide pour gérer la déconnexion. Symfony gère automatiquement la déconnexion, donc cette méthode ne nécessite pas de code
    {
        // Cette méthode sera interceptée par le système de sécurité
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}
