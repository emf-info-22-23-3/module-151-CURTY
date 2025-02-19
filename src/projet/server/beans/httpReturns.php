<?php

/**
 * Script PHP contenant toutes les réponses d'erreur utile pour le programme
 * @version 1.0
 * @author Curty Esteban
 * @project BaoBull
 */
define('BAD_REQUEST', new ErrorAnswer('The request has missing or invalid parameters.', 400));
define('UNAUTHORIZED', new ErrorAnswer('The requested action requires you to be authenticated.', 401));
define('FORBIDDEN', new ErrorAnswer('The requested action is not allowed.', 403));
define('NOT_FOUND', new ErrorAnswer('The requested resource does not exist.', 404));
define('METHOD_NOT_ALLOWED', new ErrorAnswer('The request method is not allowed.', 405));
define('CONFLICT', new ErrorAnswer('The requested action could not be completed because of a conflict.', 409));
define('UNPROCESSABLE_ENTITY', new ErrorAnswer('The provided parameters are not valid.', 422));
define('INTERNAL_SERVER_ERROR', new ErrorAnswer('An unexpected server error occurred. Please try again in a moment.', 500));
define('SERVICE_UNAVAILABLE', new ErrorAnswer('The requested service is temporarily down.', 503));
define('HTTP_SUCCESS', 200);
