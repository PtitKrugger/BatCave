# 🦇 BatCave

**BatCave** est une application web développée avec **Symfony**, inspirée de Twitter.

---

![BatCave Screenshot](/public/img/demo/demo1.png)

---

## ✨ Fonctionnalités principales

* **📝 Inscription & Connexion** : Créez un compte ou connectez-vous facilement.
* **📣 BatPosts** : Publiez vos pensées, idées ou actualités.
* **❤️ Interactions sociales** :

* **👍 BatLike** : Montrez votre appréciation pour les BatPosts.
* **💬 Réponses** : Répondez aux posts et engagez des discussions.
* **🎨 Personnalisation du BatProfil** :
  * Ajoutez une **description personnelle**.
  * Téléchargez une **photo de profil** et un **background**.
  * Personnalisez les **couleurs des contours** autour de votre photo de profil et du background.

## ⚠️ Fonctionnalités manquantes

* **📧 Vérification de l’email**
* **🔑 Mot de passe oublié**
* **✏️ Édition d’un BatPost**
* **🗑️ Suppression d’un BatPost**

## 🛠 Technologies utilisées

* **Backend** : Symfony
* **Frontend** : Twig, HTML, CSS, JavaScript
* **Base de données** : MySQL (ou PostgreSQL)

## 🚀 Comment démarrer le projet

1. **Cloner le dépôt** :

```bash
git clone https://github.com/PtitKrugger/BatCave.git
cd BatCave
```

2. **Installer les dépendances** :

```bash
composer install
npm install
npm run dev
```

3. **Configurer l’environnement** :

* Copier `.env` en `.env.local` et définir les variables suivantes :

```env
APP_ENV=dev
APP_SECRET=votre_cle_secrete
DATABASE_URL="mysql://user:password@127.0.0.1:3306/batcave_db"
```

4. **Créer la base de données et les tables** :

```bash
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
```

5. **Lancer le serveur Symfony** :

```bash
symfony server:start
```

6. **Accéder à l’application** :
   Ouvrir [http://localhost:8000](http://localhost:8000) dans votre navigateur.

---

## 📷 Aperçu 

![BatCave Screenshot](/public/img/demo/demo2.png)
![BatCave Screenshot](/public/img/demo/demo3.png)
![BatCave Screenshot](/public/img/demo/demo4.png)
