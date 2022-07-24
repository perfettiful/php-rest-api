<?php
namespace Src;

require_once '../vendor/autoload.php';

class Post
{
    private $db;
    private $requestMethod;
    private $postId;

    public function __construct($db, $requestMethod, $postId)
    {
        $this->db = $db;
        $this->requestMethod = $requestMethod;
        $this->postId = $postId;
    }

    public function processRequest()
    {
        switch ($this->requestMethod) {
            case 'GET':
                if ($this->postId) {
                    $response = $this->getPost($this->postId);
                } else {
                    $response = $this->getAllPosts();
                };
                break;
            case 'POST':
                $response = $this->createPost();
                break;
            case 'PUT':
                $response = $this->updatePost($this->postId);
                break;
            case 'DELETE':
                $response = $this->deletePost($this->postId);
                break;
            default:
                $response = $this->notFoundResponse();
                break;
        }
        header($response['status_code_header']);
        if ($response['body']) {
            echo $response['body'];
        }
    }

    private function getAllPosts()
    {
        $query = "
            SELECT
              id, title, body, author, author_picture, created_at
            FROM
              post;
        ";

        try {
            $statement = $this->db->query($query);
            $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException$e) {
            exit($e->getMessage());
        }

        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = json_encode($result);
        return $response;
    }

    private function getPost($id)
    {

        $result = $this->find($id);
        if (!$result) {
            return $this->notFoundResponse();
        }
        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = json_encode($result);
        return $response;
    }

    private function createPost()
    {

        $input = (array) json_decode(file_get_contents('php://input'), true);

        if (!$this->validatePost($input)) {
            return $this->unprocessableEntityResponse();
        }

        $query = "
            INSERT INTO post
                (title, body, author, author_picture)
            VALUES
                (:title, :body, :author, :author_picture);
          ";

        $author = $this->getEmail();

        if (is_string($author)) {
            try {
                $statement = $this->db->prepare($query);
                $statement->execute(array(
                    'title' => $input['title'],
                    'body' => $input['body'],
                    'author' => $author,
                    'author_picture' => 'https://secure.gravatar.com/avatar/' . md5(strtolower($author)) . '.png?s=200',
                ));
                $statement->rowCount();
            } catch (\PDOException$e) {
                exit($e->getMessage());
            }

            $response['status_code_header'] = 'HTTP/1.1 201 Created';
            $response['body'] = json_encode(array('message' => 'Post Created'));
            return $response;
        } else {
            return $this->didMissing();
        }

    }

    private function updatePost($id)
    {
        $result = $this->find($id);
        if (!$result) {
            return $this->notFoundResponse();
        }
        $input = (array) json_decode(file_get_contents('php://input'), true);
        if (!$this->validatePost($input)) {
            return $this->unprocessableEntityResponse();
        }

        $query = "
            UPDATE post
            SET
                title = :title,
                body  = :body,
                author = :author,
                author_picture = :author_picture
            WHERE id = :id AND author = :author;
        ";

        $author = $this->getEmail();

        if (is_string($author)) {
            try {
                $statement = $this->db->prepare($query);
                $statement->execute(array(
                    'id' => (int) $id,
                    'title' => $input['title'],
                    'body' => $input['body'],
                    'author' => $author,
                    'author_picture' => 'https://secure.gravatar.com/avatar/' . md5(strtolower($author)) . '.png?s=200',
                ));
                if ($statement->rowCount() == 0) {
                    // Different Author trying to update.
                    return $this->unauthUpdate();
                }
            } catch (\PDOException$e) {
                exit($e->getMessage());
            }
            $response['status_code_header'] = 'HTTP/1.1 200 OK';
            $response['body'] = json_encode(array('message' => 'Post Updated!'));
            return $response;
        } else {
            return $this->didMissing();
        }

    }

    private function deletePost($id)
    {
        $author = $this->getEmail();
        if (is_string($author)) {
            $result = $this->find($id);
            if (!$result) {
                return $this->notFoundResponse();
            }

            $query = "
              DELETE FROM post
              WHERE id = :id AND author = :author;
          ";

            try {
                $statement = $this->db->prepare($query);
                $statement->execute(array('id' => $id, 'author' => $author));
                if ($statement->rowCount() == 0) {
                    // Different Author trying to delete.
                    return $this->unauthDelete();
                }
            } catch (\PDOException$e) {
                exit($e->getMessage());
            }
            $response['status_code_header'] = 'HTTP/1.1 200 OK';
            $response['body'] = json_encode(array('message' => 'Post Deleted!'));
            return $response;
        } else {
            // DID Error.
            return $this->didMissing();
        }

    }

    public function find($id)
    {

        $query = "
            SELECT
                id, title, body, author, author_picture, created_at
            FROM
                post
            WHERE id = :id;
        ";

        try {
            $statement = $this->db->prepare($query);
            $statement->execute(array('id' => $id));
            $result = $statement->fetch(\PDO::FETCH_ASSOC);
            return $result;
        } catch (\PDOException$e) {
            exit($e->getMessage());
        }
    }

    private function validatePost($input)
    {
        if (!isset($input['title'])) {
            return false;
        }
        if (!isset($input['body'])) {
            return false;
        }
        return true;
    }

    private function unprocessableEntityResponse()
    {
        $response['status_code_header'] = 'HTTP/1.1 422 Unprocessable Entity';
        $response['body'] = json_encode([
            'error' => 'Invalid input',
        ]);
        return $response;
    }

    private function notFoundResponse()
    {
        $response['status_code_header'] = 'HTTP/1.1 404 Not Found';
        $response['body'] = null;
        return $response;
    }

    private function didMissing()
    {
        $response['status_code_header'] = 'HTTP/1.1 404 Not Found';
        $response['body'] = json_encode([
            'error' => 'DID is Missing on Header.',
        ]);
        return $response;
    }

    private function didMalformed()
    {
        $response['status_code_header'] = 'HTTP/1.1 404 Not Found';
        $response['body'] = json_encode([
            'error' => 'DID is Malformed',
        ]);
        return $response;
    }

    private function unauthDelete()
    {
        $response['status_code_header'] = 'HTTP/1.1 404 Not Found';
        $response['body'] = json_encode([
            'error' => 'You are not authorised to delete this post.',
        ]);
        return $response;
    }

    private function unauthUpdate()
    {
        $response['status_code_header'] = 'HTTP/1.1 404 Not Found';
        $response['body'] = json_encode([
            'error' => 'You are not authorised to update this post.',
        ]);
        return $response;
    }

    public function getEmail()
    {
        if (function_exists('getallheaders')) {

            $did_token = \MagicAdmin\Util\Http::parse_authorization_header_value(getallheaders()['Authorization']);

            // DIDT is missing from the original HTTP request header. 404: DID Missing
            if ($did_token == null) {
                return $this->didMissing();
            }

            $magic = new \MagicAdmin\Magic($_ENV['MAGIC_SECRET_KEY']);

            try {
                $magic->token->validate($did_token);
                $issuer = $magic->token->get_issuer($did_token);
                $user_meta = $magic->user->get_metadata_by_issuer($issuer);
                return $user_meta->data->email;
            } catch (\MagicAdmin\Exception\DIDTokenException$e) {
                // DIDT is malformed.
                return $this->didMalformed();
            }
        }
    }
}
