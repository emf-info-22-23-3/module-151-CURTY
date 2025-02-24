<?php
/**
 * Class HttpError
 * 
 * Cette classe fournit des méthodes statiques pour retourner des objets d'erreur standardisés,
 * correspondant à des codes d'état HTTP courants. Chaque méthode retourne une instance de la classe `ErrorAnswer`
 * avec un message d'erreur approprié et le code HTTP correspondant.
 */
class HttpReturns {
    
    /**
     * Retourne un objet `ErrorAnswer` pour une erreur 400 (Bad Request).
     *
     * Cette erreur est renvoyée lorsque la requête est malformée ou a des paramètres manquants ou invalides.
     *
     * @return ErrorAnswer L'objet `ErrorAnswer` pour une erreur 400.
     */
    public static function BAD_REQUEST() {
        return new ErrorAnswer('The request has missing or invalid parameters.', 400);
    }

    /**
     * Retourne un objet `ErrorAnswer` pour une erreur 401 (Unauthorized).
     *
     * Cette erreur est renvoyée lorsque l'action demandée nécessite une authentification de l'utilisateur.
     *
     * @return ErrorAnswer L'objet `ErrorAnswer` pour une erreur 401.
     */
    public static function UNAUTHORIZED() {
        return new ErrorAnswer('The requested action requires you to be authenticated.', 401);
    }

    /**
     * Retourne un objet `ErrorAnswer` pour une erreur 403 (Forbidden).
     *
     * Cette erreur est renvoyée lorsque l'utilisateur n'est pas autorisé à effectuer l'action demandée.
     *
     * @return ErrorAnswer L'objet `ErrorAnswer` pour une erreur 403.
     */
    public static function FORBIDDEN() {
        return new ErrorAnswer('The requested action is not allowed.', 403);
    }

    /**
     * Retourne un objet `ErrorAnswer` pour une erreur 404 (Not Found).
     *
     * Cette erreur est renvoyée lorsque la ressource demandée n'existe pas.
     *
     * @return ErrorAnswer L'objet `ErrorAnswer` pour une erreur 404.
     */
    public static function NOT_FOUND() {
        return new ErrorAnswer('The requested resource does not exist.', 404);
    }

    /**
     * Retourne un objet `ErrorAnswer` pour une erreur 405 (Method Not Allowed).
     *
     * Cette erreur est renvoyée lorsque la méthode HTTP utilisée dans la requête n'est pas autorisée pour cette ressource.
     *
     * @return ErrorAnswer L'objet `ErrorAnswer` pour une erreur 405.
     */
    public static function METHOD_NOT_ALLOWED() {
        return new ErrorAnswer('The request method is not allowed.', 405);
    }

    /**
     * Retourne un objet `ErrorAnswer` pour une erreur 409 (Conflict).
     *
     * Cette erreur est renvoyée lorsque la requête ne peut pas être traitée en raison d'un conflit avec l'état actuel de la ressource.
     *
     * @return ErrorAnswer L'objet `ErrorAnswer` pour une erreur 409.
     */
    public static function CONFLICT() {
        return new ErrorAnswer('The requested action could not be completed because of a conflict.', 409);
    }

    /**
     * Retourne un objet `ErrorAnswer` pour une erreur 422 (Unprocessable Entity).
     *
     * Cette erreur est renvoyée lorsque les paramètres fournis ne sont pas valides ou ne peuvent pas être traités par le serveur.
     *
     * @return ErrorAnswer L'objet `ErrorAnswer` pour une erreur 422.
     */
    public static function UNPROCESSABLE_ENTITY() {
        return new ErrorAnswer('The provided parameters are not valid.', 422);
    }

    /**
     * Retourne un objet `ErrorAnswer` pour une erreur 500 (Internal Server Error).
     *
     * Cette erreur est renvoyée lorsque le serveur rencontre une erreur inattendue lors du traitement de la requête.
     *
     * @return ErrorAnswer L'objet `ErrorAnswer` pour une erreur 500.
     */
    public static function INTERNAL_SERVER_ERROR() {
        return new ErrorAnswer('An unexpected server error occurred. Please try again in a moment.', 500);
    }

    /**
     * Retourne un objet `ErrorAnswer` pour une erreur 503 (Service Unavailable).
     *
     * Cette erreur est renvoyée lorsque le service est temporairement indisponible.
     *
     * @return ErrorAnswer L'objet `ErrorAnswer` pour une erreur 503.
     */
    public static function SERVICE_UNAVAILABLE() {
        return new ErrorAnswer('The requested service is temporarily down.', 503);
    }

    /**
     * Retourne le code HTTP pour une requête réussie (200 OK).
     *
     * @return int Le code de statut HTTP 200 (OK).
     */
    public static function HttpSuccess() {
        return 200;
    }
}
?>
