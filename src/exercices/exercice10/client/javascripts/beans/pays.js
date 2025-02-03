/*
 * Bean "Pays".
 *
 */

var Pays = function() {
};

/**
 * Setter pour le nom
 * @param String nom
 * @returns {undefined}
 */
Pays.prototype.setNom = function(nom) {
  this.nom = nom;
};

/**
 * Setter pour le pk du pays
 * @param String nom
 * @returns {undefined}
 */
Pays.prototype.setPk = function(pk) {
  this.pk = pk;
};

/**
 * Retourne le pays en format texte
 * @returns Le pays en format texte
 */
Pays.prototype.toString = function () {
  return this.nom;
};

