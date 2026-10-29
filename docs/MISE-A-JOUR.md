# Mise à jour du site ERIS SARL – déploiement, messagerie et contrôles

Ce document accompagne la mise à jour corrigeant les points du diagnostic fonctionnel.

## 0. Travailler en local (base SQLite de test)

Le fichier `.env.local` (ignoré par Git) remplace les réglages de `.env` sur le poste de développement :
base SQLite `var/data_dev.db`, e-mails désactivés, mode debug. **Ne jamais l'envoyer sur le serveur.**

```bash
php bin/console app:dev:seed   # (re)crée la base SQLite et charge les données de test
composer serve                 # lance le site sur http://127.0.0.1:8000
npm run build:css              # après une modification des classes Tailwind
```

Compte administrateur de test : `admin@eris.local` / `admin1234` (page `/login`).
La commande `app:dev:seed` refuse de s'exécuter sur une base autre que SQLite : elle ne peut pas vider la production.

## 1. Déploiement sur l'hébergement (LWS / cPanel)

1. Envoyer les fichiers (Git ou FTP), puis dans le terminal cPanel, à la racine du projet :

   ```bash
   composer install --no-dev --optimize-autoloader     # installe liip/imagine-bundle
   php bin/console doctrine:migrations:migrate -n      # nouvelle migration Version20261029080000
   php bin/console cache:clear
   php bin/console app:images:optimize --dry-run       # liste les photos trop lourdes
   php bin/console app:images:optimize                 # les réduit (max 2400 px) – faire une sauvegarde avant
   ```

2. Vérifier que `public/media/cache/` est accessible en écriture (miniatures générées automatiquement).
3. Le CSS est déjà compilé dans `public/css/app.css`. Après une modification des classes Tailwind dans
   `templates/`, recompiler avec `npm run build:css`, puis augmenter `framework.assets.version`
   dans `config/packages/framework.yaml`.

### Variables à définir dans `.env.local` (sur le serveur, jamais dans Git)

```dotenv
# Compte e-mail utilisé par le site pour envoyer (port 465 = SSL)
MAILER_DSN=smtp://no-reply%40eris-sarl.com:MOT_DE_PASSE@mail.eris-sarl.com:465
MAILER_FROM=no-reply@eris-sarl.com
# Adresse qui reçoit les demandes du formulaire (affichée aussi sur le site)
CONTACT_EMAIL=contact@eris-sarl.com
```

Le `@` du nom d'utilisateur doit être écrit `%40`. Les caractères spéciaux du mot de passe doivent être encodés
de la même façon (`#` → `%23`, `/` → `%2F`, etc.).

## 2. Messagerie @eris-sarl.com (phase 2 du diagnostic)

### État DNS constaté le 29/10/2026

| Élément | Valeur | État |
|---|---|---|
| Hébergeur | LWS (serveur `web54.lws-hosting.com`, cPanel) | – |
| MX | `mail.eris-sarl.com` (91.234.195.128) | ✅ |
| SPF | `v=spf1 ip4:91.234.195.128 ip4:91.234.195.186 include:premiumsmtp.dnshostservices.com +a +mx ~all` | ✅ |
| DKIM | sélecteur `default._domainkey` présent (RSA 2048) | ✅ |
| DMARC | `v=DMARC1; p=quarantine;` | ⚠️ pas d'adresse de rapport |
| Autodiscover | SRV vers `cpanelemaildiscovery.cpanel.net` | ✅ |

### À faire dans l'espace client LWS / cPanel

1. **Créer les comptes** (cPanel → Comptes de messagerie) : au minimum `contact@` (réception du formulaire)
   et `no-reply@` (envoi par le site). Selon les besoins : `devis@`, `rh@`, adresses nominatives.
   Fixer un quota (ex. 2 Go), un mot de passe fort et noter qui y a accès.
2. **Compléter DMARC** : remplacer l'enregistrement TXT `_dmarc` par
   `v=DMARC1; p=quarantine; rua=mailto:contact@eris-sarl.com; fo=1`
   pour recevoir les rapports d'échec d'authentification.
3. **Vérifier DKIM/SPF** dans cPanel → « Délivrabilité des e-mails » : tout doit être « Valide ».

### Configurer Gmail / Outlook / téléphone

| Paramètre | Valeur |
|---|---|
| Serveur entrant IMAP | `mail.eris-sarl.com`, port **993**, SSL/TLS |
| Serveur sortant SMTP | `mail.eris-sarl.com`, port **465**, SSL/TLS (ou 587 STARTTLS) |
| Identifiant | adresse complète (ex. `contact@eris-sarl.com`) |
| Webmail | `https://webmail.eris-sarl.com` (ou depuis cPanel) |

- **Outlook** : « Ajouter un compte » → l'autodiscover remplit souvent les paramètres seul ; sinon choisir IMAP.
- **Gmail** : Paramètres → Comptes → « Consulter d'autres comptes » (Gmailify/POP) et « Envoyer des e-mails
  en tant que » avec le SMTP ci-dessus. Gmail récupère les messages toutes les ~30 min : pour une réception
  immédiate, préférer l'application Gmail mobile en IMAP, Outlook ou Thunderbird.

## 3. Formulaire de contact – fonctionnement

- Chaque message est **enregistré en base** puis notifié par e-mail à `CONTACT_EMAIL` (avec « Répondre à » =
  le visiteur) ; le visiteur reçoit un accusé de réception.
- Admin → **Messages reçus** liste toutes les demandes ; la mention « E-mail non envoyé » signale un problème
  de messagerie (voir `var/log/prod.log`).
- Protections : jeton CSRF, champ piège anti-robots, délai minimal de saisie, 5 messages max par heure et par IP,
  bouton désactivé après le clic, redirection après envoi (un rafraîchissement ne renvoie pas le message).
- Lien direct avec type présélectionné : `/contact?sujet=devis|partenariat|information|candidature`.

## 4. Contrôles à refaire après mise en ligne (phase 5)

- [ ] Envoyer un message test depuis `/contact` → réception sur `CONTACT_EMAIL` + accusé de réception ;
      le message apparaît dans Admin → Messages reçus avec « E-mail envoyé ».
- [ ] Échanges `contact@eris-sarl.com` ⇄ Gmail, Outlook/Hotmail, Yahoo : pas de rejet ni de spam.
      Score sur https://www.mail-tester.com (objectif ≥ 9/10).
- [ ] Menu mobile : ouverture, fermeture (croix, clic à côté, touche Échap), navigation.
- [ ] Liens réseaux sociaux du pied de page (seules les URL valides sont affichées).
- [ ] PageSpeed Insights (https://pagespeed.web.dev) sur l'accueil et une page service, mobile et ordinateur.
- [ ] Google Search Console : déclarer `https://eris-sarl.com/sitemap.xml`.
- [ ] Admin → Réalisations : rattacher chaque projet à son service (champ « Service concerné »).
- [ ] Renseigner l'adresse exacte du bureau de Bukavu (`templates/contact/index.html.twig`).
