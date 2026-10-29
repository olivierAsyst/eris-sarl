<?php

namespace App\Command;

use App\Entity\ContactMessage;
use App\Entity\HomeVideo;
use App\Entity\ImageGalery;
use App\Entity\Member;
use App\Entity\Ournumber;
use App\Entity\Partner;
use App\Entity\Realisation;
use App\Entity\Services;
use App\Entity\SocialMedia;
use App\Entity\User;
use Doctrine\DBAL\Platforms\SQLitePlatform;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * Recrée la base SQLite locale et la remplit avec des données de démonstration.
 *
 *   php bin/console app:dev:seed
 *
 * Sécurité : la commande refuse de s'exécuter si la base n'est pas SQLite
 * (elle ne peut donc jamais effacer la base MySQL de production).
 */
#[AsCommand(name: 'app:dev:seed', description: 'Recrée la base SQLite locale et charge les données de test')]
final class DevSeedCommand extends Command
{
    public const ADMIN_EMAIL = 'admin@eris.local';
    public const ADMIN_PASSWORD = 'admin1234';

    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly UserPasswordHasherInterface $hasher,
        #[Autowire('%kernel.project_dir%/public')] private readonly string $publicDir,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $conn = $this->em->getConnection();

        if (!$conn->getDatabasePlatform() instanceof SQLitePlatform) {
            $io->error('Cette commande ne fonctionne que sur une base SQLite locale (sécurité). Vérifiez DATABASE_URL dans .env.local.');

            return Command::FAILURE;
        }

        // 1. Schéma : on repart de zéro
        $metadata = $this->em->getMetadataFactory()->getAllMetadata();
        $tool = new SchemaTool($this->em);
        $tool->dropDatabase();
        $tool->createSchema($metadata);
        $io->writeln('✔ Schéma créé');

        // 2. Administrateur
        $admin = (new User())->setEmail(self::ADMIN_EMAIL)->setFullName('Administrateur local')->setRoles(['ROLE_ADMIN']);
        $admin->setPassword($this->hasher->hashPassword($admin, self::ADMIN_PASSWORD));
        $this->em->persist($admin);

        // 3. Services
        $services = [];
        foreach ($this->servicesData() as $key => [$name, $description, $image]) {
            $s = (new Services())->setName($name)->setDescription($description);
            $this->setFile($s, 'setImageUrl', 'images/services', $image);
            $this->em->persist($s);
            $services[$key] = $s;
        }

        // 4. Réalisations, reliées aux services
        foreach ($this->realisationsData() as [$name, $description, $image, $serviceKey]) {
            $r = (new Realisation())->setName($name)->setDescription($description)->setService($services[$serviceKey] ?? null);
            $this->setFile($r, 'setImageUrl', 'images/realisations', $image);
            $this->em->persist($r);
        }

        // 5. Galerie
        foreach ([
            ['Goma', "Chantier d'adduction d'eau potable – l'équipe sur site", 'img-1693-6889e20f28aee946155311.jpg'],
            ['Runingu', 'Reconnaissance du terrain pour les études hydro-agricoles', 'terrain-6889df19dd61f472509267.jpg'],
            ['Nord-Kivu', 'Suivi des travaux de canalisation', 'img-2156-6889e0fdc9d8e055126554.jpg'],
            ['Goma', 'Bâtiment en construction', 'iko-insulations-maison-individuelle-68760d6851c00303458638.jpg'],
            ['Bukavu', "Réunion de chantier avec les partenaires", 'fb-img-1654202824205-6873e3336694a741975378.jpg'],
            ['Goma', 'Équipe technique ERIS', 'juveain-687144620cd72525074599.jpg'],
            ['Rubanga', null, 'rj-kanierra-68713f63ee21f423910111.jpg'],
        ] as [$lieu, $desc, $image]) {
            $g = (new ImageGalery())->setLieu($lieu)->setDescription($desc);
            $this->setFile($g, 'setImageUrl', 'images/galerie', $image);
            $this->em->persist($g);
        }

        // 6. Équipe
        foreach ([
            ['Alain SANTA NGABO', 'Gérant – Ingénieur civil', "Plus de 10 ans d'expérience dans la conception et le suivi d'ouvrages hydrauliques et de bâtiments.", 'whatsapp-image-2025-07-22-at-14-23-03-6881012ec7c88218881785.jpg', 'https://www.linkedin.com/'],
            ['Ingénieure hydraulique', 'Responsable eau & assainissement', 'Spécialiste des réseaux AEP et de l’assainissement en contexte humanitaire.', '132018682-3796632800396058-6747809258780892682-n-687f68beabf4d934451286.jpg', null],
            ['Technicien géomètre', 'Topographie & SIG', 'Levés topographiques, cartographie et modélisation des bassins versants.', null, null],
            ['Agronome', 'Aménagements hydro-agricoles', "Accompagnement des coopératives dans l'irrigation et la gestion des sols.", null, 'https://www.facebook.com/'],
        ] as [$name, $fonction, $desc, $image, $link]) {
            $m = (new Member())->setFullName($name)->setFonction($fonction)->setDescription($desc);
            $this->setFile($m, 'setImageUrl', 'images/member', $image);
            if ($link && str_contains($link, 'linkedin')) {
                $m->setLinkedinlink($link);
            } elseif ($link) {
                $m->setFacebookLink($link);
            }
            $this->em->persist($m);
        }

        // 7. Partenaires
        foreach ([
            ['YWAM DRC', 'ywam-drc-6918ed9ecf518352405754.png', 'https://www.ywam.org/'],
            ['AVUDS', 'avuds-6918ec27ba9cb578776244.png', null],
            ['ZOA', 'zoa-v-6918ea6b084d6232716376.png', 'https://www.zoa-international.com/'],
        ] as [$name, $logo, $link]) {
            $p = (new Partner())->setName($name)->setLinks($link);
            $this->setFile($p, 'setLogo', 'images/partner', $logo);
            $this->em->persist($p);
        }

        // 8. Réseaux sociaux (un lien volontairement invalide : il ne doit pas s'afficher)
        foreach ([
            ['LinkedIn', 'https://www.linkedin.com/', 'linkedin-68777521e3219089771851.png'],
            ['X (Twitter)', 'https://x.com/', 'twitter-new-logo-687f71f265974479170391.jpg'],
            ['Lien cassé (test)', 'pas-une-url', null],
        ] as [$name, $lien, $icon]) {
            $sm = (new SocialMedia())->setName($name)->setLien($lien);
            $this->setFile($sm, 'setIcon', 'images/social', $icon);
            $this->em->persist($sm);
        }

        // 9. Chiffres clés
        $this->em->persist((new Ournumber())->setYear('2021')->setProjects('35')->setExperts('12')->setPartner('10'));

        // 10. Vidéo d'accueil (si un fichier est présent)
        $videos = glob($this->publicDir.'/videos/home_video/*.mp4') ?: [];
        if ($videos) {
            $this->em->persist((new HomeVideo())->setName('Présentation')->setVideoUrl(basename($videos[0])));
        }

        // 11. Exemple de message reçu
        $this->em->persist((new ContactMessage())
            ->setSubjectType('devis')->setFullName('Jean Mukendi')->setEmail('jean.mukendi@example.com')
            ->setPhone('+243 970 000 000')->setOrganization('Coopérative agricole de Rubanga')
            ->setMessage("Bonjour,\n\nNous souhaitons un devis pour l'étude d'un système d'irrigation de 20 ha.\n\nMerci.")
            ->setMailSent(true));

        $this->em->flush();

        $io->success('Base de test prête.');
        $io->table(['Accès administration', ''], [
            ['URL', '/login'],
            ['E-mail', self::ADMIN_EMAIL],
            ['Mot de passe', self::ADMIN_PASSWORD],
        ]);

        return Command::SUCCESS;
    }

    /** Associe un fichier image existant (sans upload) s'il est présent sur le disque. */
    private function setFile(object $entity, string $setter, string $dir, ?string $file): void
    {
        if ($file && is_file($this->publicDir.'/'.$dir.'/'.$file)) {
            $entity->$setter($file);
        }
    }

    /** @return array<string, array{string, string, ?string}> */
    private function servicesData(): array
    {
        return [
            'genie' => ['Génie civil & Infrastructures', <<<TXT
                Notre approche : nous accompagnons nos partenaires dans la conception, la réalisation et le suivi d'infrastructures durables, adaptées aux contraintes du terrain et aux ressources disponibles.

                Nos prestations :
                - Études techniques et plans d'exécution (bâtiments, ouvrages d'art, voiries)
                - Métrés, devis quantitatifs et estimatifs
                - Direction et surveillance de travaux
                - Contrôle qualité des matériaux et réception des ouvrages

                Chaque projet est mené avec une attention particulière à la sécurité, à la durabilité et à l'implication des communautés locales.
                TXT, 'img-5478-691b4eea2c358558159446.jpg'],
            'eau' => ['Eau potable & Assainissement', <<<TXT
                Spécialiste de l'Approvisionnement en Eau Potable (AEP) et de l'Assainissement, ERIS SARL conçoit et réalise des systèmes fiables pour les villes, les villages et les camps de déplacés.

                - Captages de sources, forages et adductions gravitaires
                - Réseaux de distribution et bornes-fontaines
                - Latrines, douches et blocs sanitaires adaptés
                - Gestion des boues et des eaux usées

                Nous intégrons la formation des comités de gestion pour garantir la pérennité des ouvrages.
                TXT, '20240831-100903-691ddbac2dfda101438891.jpg'],
            'gire' => ['Gestion intégrée des eaux et des sols', <<<TXT
                Notre approche : la gestion intégrée des ressources en eau (GIRE) exige une connaissance approfondie des bassins versants, de leur hydrologie et de leurs usages.

                - Diagnostic et cartographie des bassins versants
                - Lutte antiérosive et conservation des sols
                - Plans de gestion concertée de la ressource
                TXT, 'iko-insulations-maison-individuelle-68760ff5270fd919067400.jpg'],
            'climat' => ['Résilience climatique', <<<TXT
                Gérer les eaux pour prévenir les inondations et la sécheresse. La résilience climatique repose sur une bonne compréhension des risques et sur des aménagements adaptés.

                - Études de vulnérabilité et cartographie des risques
                - Ouvrages de protection contre les crues et le ravinement
                - Stockage de l'eau de pluie et recharge des nappes
                TXT, 'copilot-20260329-142250-69c91ac448ddd620582680.png'],
            'agri' => ['Eau & Agriculture', <<<TXT
                Votre partenaire en irrigation et aménagements hydro-agricoles : depuis plus de 4 ans, nous accompagnons coopératives, ONG et institutions dans la valorisation agricole de l'eau.

                - Études de faisabilité des périmètres irrigués
                - Conception de barrages, prises d'eau et canaux
                - Appui à la mise en valeur et à l'organisation des usagers
                TXT, '11750-big-1-68753dd65ee65663332569.jpg'],
        ];
    }

    /** @return list<array{string, string, ?string, ?string}> */
    private function realisationsData(): array
    {
        return [
            ['Projet de construction de la base YWAM DRC', "Réalisation d'un bâtiment administratif R+1 (extensible à R+4 suivant financement) pour l'organisation YWAM DRC à Goma.\n\nMissions : conception architecturale et structurelle, plans d'exécution, devis estimatif et suivi des travaux.\n\nRésultat : un bâtiment fonctionnel, conçu pour évoluer avec les besoins de l'organisation.", 'base-ywam-drc-691b47a41a7dd726249345.png', 'genie'],
            ["Projet d'assainissement du camp de Lushagala", "Mise en place d'infrastructures sanitaires adaptées pour améliorer les conditions d'hygiène dans le camp de déplacés de Lushagala, en partenariat avec AVUDS.\n\n- Construction de blocs de latrines et douches séparés hommes / femmes\n- Dispositifs de lavage des mains\n- Sensibilisation à l'hygiène", 'toil-avuds-6918ff1dcd25b832022096.jpg', 'eau'],
            ['Études hydroagricoles à Runingu et Rubanga', "Études de faisabilité des aménagements hydroagricoles à Runingu et Rubanga : analyse hydrologique et topographique, dimensionnement d'un barrage de dérivation et du réseau de canaux, modélisation 3D de l'ouvrage.", 'vue-3d-du-barrage-de-runingu-projet-6918e0aa02813724597807.jpg', 'agri'],
            ['Réhabilitation d’ouvrages de génie civil', "Diagnostic et réhabilitation d'ouvrages existants : renforcement structurel, reprise des fondations et mise aux normes.", 'geniecivile-68765d8998bf6395333580.png', 'genie'],
        ];
    }
}
