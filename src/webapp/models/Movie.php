<?php

namespace tdt4237\webapp\models;

class Movie
{
    private $id;
    private $name;
    private $imageUrl;

    static $app;

    public function getId()
    {
        return $this->id;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getImageUrl()
    {
        return $this->imageUrl;
    }

    static function make($id, $name, $imageUrl)
    {
        $movie = new Movie();
        $movie->id = $id;
        $movie->name = $name;
        $movie->imageUrl = $imageUrl;

        return $movie;
    }

    /**
     * Find a movie by id.
     */
    static function find($id)
    {
        $stmt = self::$app->db->prepare("SELECT * FROM movies WHERE id = ?");
        $stmt->bindParam(1, $id);
        $stmt->execute();
        $row = $stmt->fetch();
        if($row == null){
            return null;
        }

        return self::makeFromRow($row);
    }

    /**
     * Fetch all movies.
     */
    static function all()
    {
        $query = "SELECT * FROM movies";
        $results = self::$app->db->query($query);

        $movies = [];

        foreach ($results as $row) {
            $movie = self::makeFromRow($row);
            array_push($movies, $movie);
        }

        return $movies;
    }

    static function makeFromRow($row) {
        $movie = self::make(
            $row['id'],
            $row['name'],
            $row['imageurl']
        );

        return $movie;
    }
}
Movie::$app = \Slim\Slim::getInstance();
