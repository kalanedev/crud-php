<?php

class Cli{

    const ACTION_LIST = 1;
    const ACTION_CREATE = 2;
    const ACTION_UPDATE = 3;
    const ACTION_DELETE = 4;
    const ACTION_EXIT = 5;

    private $pdo;

    public function __construct()
    {
        $this->pdo = new PDO('mysql:host=localhost;dbname=gatas', "larissa", "");
    }

    private function line()
    {
        echo "------------------------------" . PHP_EOL;
    }

    private function availableOptions()
    {
        $this->output("Gatas CLI");
        $this->line();
        $this->output("1 - Listar gatas");
        $this->output("2 - Virar uma gata");
        $this->output("3 - Atualizar gata");
        $this->output("4 - Deixar de ser uma gata");
        $this->output("5 - Sair");
        $this->line();
        $this->output("Selecione uma opção: ");
    }

    public function run() 
    {
        while (true) {   
            
            $this->availableOptions();
            $choice = (int)$this->input();

            match ($choice) {
                self::ACTION_EXIT => $this->exit(),
                self::ACTION_CREATE => $this->create(),
                self::ACTION_LIST => $this->list(),
                self::ACTION_DELETE => $this->delete(),
                self::ACTION_UPDATE => $this->update(),
                default => null,
            };

        }
    }

    private function exit()
    {
        $this->output("Tchau gata");
        die;
    }
    
    private function create()
    {
        $this->output("Qual o seu nome, gata?");
        $name = $this->input();
        $this->output("Quando você nasceu, gata?");
        $birthdate = $this->input();
        $this->output("O que você estuda, gata?");
        $about = $this->input();
        $this->output("Qual seu twitter, gata?");
        $twitterUrl = $this->input();

        $this->line();
        $this->output("Nome: $name");
        $this->output("Data de Nascimento: $birthdate");
        $this->output("Sobre: $about");
        $this->output("Twitter: $twitterUrl");
        $this->line();

        $this->output("Você quer virar uma gata? (s/n)");
        $choice = $this->input();

        if ($choice == "s") {
            $q = "INSERT INTO users VALUES (null, :name, :birthdate, :about, :twitterUrl)";
            $query = $this->pdo->prepare($q);
            $query->bindParam(':name', $name, PDO::PARAM_STR);
            $query->bindParam(':birthdate', $birthdate, PDO::PARAM_STR);
            $query->bindParam(':about', $about, PDO::PARAM_STR);
            $query->bindParam(':twitterUrl', $twitterUrl, PDO::PARAM_STR);

            $query->execute();
        }
    }

    private function list()
    {
        $q = "select * from users";
        $query = $this->pdo->prepare($q);
        $query->execute();

        if ($query->rowCount() == 0) {
            $this->output("Não tem gata :(");
            $this->line();
            return;
        }
        $gatas = $query->fetchAll(PDO::FETCH_ASSOC);

        $this->line();
        $this->output("Listando gatas...");
        foreach ($gatas as $gata) {
            $this->output($gata['id'] . ' ' . $gata['name'] . ' ' . $gata['about'] . ' ' . $gata['twitter_url']);
        }
        $this->line();
    }

    private function delete()
    {
        $this->output("Informe o ID da gata:");
        $idGata = $this->input();

        $q = "select id from users where id = :idGata";
        $query = $this->pdo->prepare($q);
        $query->bindParam(':idGata', $idGata, PDO::PARAM_INT);
        $query->execute();

        if ($query->rowCount() == 0) {
            $this->output("Gata inexistente :(");
            $this->line();
            return;
        }

        $q = "DELETE FROM users WHERE id = :idGata";
        $query = $this->pdo->prepare($q);
        $query->bindParam(':idGata', $idGata, PDO::PARAM_INT);
        $query->execute();
        $this->output("Tchau gata!");
        $this->line();
    }
    
    private function update()
    {
        $this->output("Informe o ID da gata:");
        $idGata = $this->input();

        $q = "select * from users where id = :idGata";
        $query = $this->pdo->prepare($q);
        $query->bindParam(':idGata', $idGata, PDO::PARAM_INT);
        $query->execute();

        if ($query->rowCount() == 0) {
            $this->output("Gata inexistente :(");
            $this->line();
            return;
        }

        
        $gata = $query->fetch(PDO::FETCH_ASSOC);
        $this->output("Não digite nada se não quiser atualizar o campo");

        $this->output("Qual o seu nome, gata? (" . $gata['name'] . "):");
        $name = $this->input();
        $name = empty($name) ? $gata['name'] : $name;

        $this->output("Quando você nasceu, gata? (" . $gata['birthdate'] . ")");
        $birthdate = $this->input();
        $birthdate = empty($birthdate) ? $gata['birthdate'] : $birthdate;
        
        $this->output("O que você estuda, gata? (" . $gata['about'] . ")");
        $about = $this->input();
        $about = empty($about) ? $gata['about'] : $about;

        $this->output("Qual seu twitter, gata? (" . $gata['twitter_url'] . ")");
        $twitterUrl = $this->input();
        $twitterUrl = empty($twitterUrl) ? $gata['twitter_url'] : $twitterUrl;

        $q = "update users set name = :name, birthdate = :birthdate, about = :about, twitter_url = :twitter_url WHERE id = :id";
        $query = $this->pdo->prepare($q);
        $query->bindParam(':name', $name, PDO::PARAM_STR);
        $query->bindParam(':birthdate', $birthdate, PDO::PARAM_STR);
        $query->bindParam(':about', $about, PDO::PARAM_STR);
        $query->bindParam(':twitter_url', $twitterUrl, PDO::PARAM_STR);
        $query->bindParam(':id', $idGata, PDO::PARAM_INT);
        $query->execute();

        $this->output("Gata atualizada com sucesso");
        $this->line();
    }

    private function input() 
    {
        return trim(fgets(STDIN));
    }

    private function output(string $message)
    {
        echo $message . PHP_EOL;
    }
}