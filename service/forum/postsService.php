<?php

class PostService {

	private $db;
	private $session;
	private $page_size = 15;
	private $logger;

	public function __construct($db, $session, $logger)
    {
        $this->db = $db;
        $this->session = $session;
        $this->logger = $logger;
    }

    public function getBlankTopic($topic_id, $perso_id) {
		$post = array();
		$post['id'] = '';
		$post['topic_id'] = $section_id;
		$post['perso_id'] = $section_id;
		$post['content'] = '';
		return $post;
    }

    public function getFormPost($request) {
		$post = array();
		$post['id'] = '';
		$post['topic_id'] = $request->get('topic_id');
		$post['perso_id'] = $request->get('perso_id');
		$post['content'] = $request->get('content');
		return $post;
    }

    public function getPost($post_id) {
    	$sql = "SELECT * FROM posts
				WHERE id = :post";

    	return $this->db->fetchAssoc($sql, array("post" => $post_id));
    }


    public function getNbPost() {
        $sql = "SELECT count(*)
                FROM posts
                ";
        return $this->db->fetchColumn($sql, array(), 0);
    }


    public function getTop10Post() {
        $sql = "SELECT user.username, count(posts.id) as cpt
                FROM user
                JOIN posts
                ON
                user.id = posts.user_id
                GROUP BY user.username
                ORDER BY cpt DESC
                LIMIT 0, 10
                ";
    	return $this->db->fetchAll($sql,
    			array()
    		);
    }

    public function getPageOfPost($topic_id, $post_id) {
    	$sql = "SELECT ( (posts.num_post - 1) DIV :page_size) as page
				FROM
				(
					SELECT @i := @i+1 as num_post, post.id as id
					FROM
					posts as post,
					(SELECT @i := 0) as it
					WHERE post.topic_id = :topic_id
					ORDER BY id DESC
				) as posts
    			WHERE post.id = :post_id";

    	return $this->db->fetchColumn($sql, array("topic_id" => $topic_id, "post_id" => $post_id, "page_size" => $this->page_size));
    }

    public function getLastPageOfPost($topic_id) {
    	$sql = "SELECT IFNULL( (MAX(posts.num_post) - 1) DIV :page_size, 0) + 1 as page
				FROM
				(
					SELECT @i := @i+1 as num_post, post.id as id
					FROM
					posts as post,
					(SELECT @i := 0) as it
					WHERE post.topic_id = :topic_id
					ORDER BY id
				) as posts";

    	return $this->db->fetchColumn($sql, array("topic_id" => $topic_id, "page_size" => $this->page_size));
    }
    
    public function getAllPostsInTopic($topic_id) {
    	$sql = "SELECT
    				post.id AS post_id,
    				post.content AS post_content,
    				post.create_date AS post_date,
    				user.id AS user_id,
    				user.username AS user_username,
    				user.avatar AS user_avatar,
    				user.profil AS user_profil,
                    user.titre AS user_titre,
    				topic.id AS topic_id,
    				topic.title AS topic_title,
    				perso.id AS perso_id,
    				perso.name AS perso_name,
                    perso.avatar AS perso_avatar
    			FROM posts post
    			JOIN topics topic
    				ON topic.id = post.topic_id
    			LEFT JOIN user user
    				ON user.id = post.user_id
    			LEFT JOIN personnages perso
    				ON perso.id = post.perso_id
				WHERE topic_id = :topic
    			ORDER BY post.id ASC
    			";

    	return $this->db->fetchAll($sql,
    			array("topic" => $topic_id)
    		);
    }
    
    public function getPostsInTopic($topic_id, $page) {
    	$debutPage = ( $page - 1) * $this->page_size;
    	$sql = "SELECT * FROM ( SELECT
    				post.id AS post_id,
    				post.content AS post_content,
    				post.create_date AS post_date,
    				user.id AS user_id,
    				user.username AS user_username,
    				user.avatar AS user_avatar,
    				user.profil AS user_profil,
                    user.titre AS user_titre,
    				topic.id AS topic_id,
    				topic.title AS topic_title,
    				perso.id AS perso_id,
    				perso.name AS perso_name,
                    perso.concept AS perso_concept,
                    perso.avatar AS perso_avatar
    			FROM posts post
    			JOIN topics topic
    				ON topic.id = post.topic_id
    			LEFT JOIN user user
    				ON user.id = post.user_id
    			LEFT JOIN personnages perso
    				ON perso.id = post.perso_id
				WHERE topic_id = :topic
    			ORDER BY post.id DESC
    			LIMIT ". $debutPage . ", " . $this->page_size . ") AS posts
                ORDER BY post_id ASC";

    	return $this->db->fetchAll($sql,
    			array("topic" => $topic_id)
    		);
    }


    public function createPost($request) {
		$sql = "INSERT INTO posts
				(topic_id, perso_id, user_id, content)
				VALUES
				(:topic,:perso,:user,:content)";

		$stmt = $this->db->prepare($sql);
		$stmt->bindValue("topic", $request->get('topic_id'));
		if($request->get('perso_id') != '') {
			$stmt->bindValue("perso", $request->get('perso_id'));
		} else {
			$stmt->bindValue("perso", null);
		}
		$stmt->bindValue("user", $this->session->get('user')['id']);
		$stmt->bindValue("content", $request->get('content'));
		$stmt->execute();

		$sql = "SELECT max(id)
				FROM posts
				WHERE topic_id = :topic_id";
		return $this->db->fetchColumn($sql, array("topic_id" => $request->get('topic_id')), 0);
    }
    
    
    public function deleteDraft($request) {
            $sql = "DELETE FROM draft
                    WHERE 
                    topic_id = :topic
                    AND user_id = :user";

            $stmt = $this->db->prepare($sql);
            $stmt->bindValue("topic", $request->get('topic_id'));
            $stmt->bindValue("user", $this->session->get('user')['id']);
            $stmt->execute();
    }
                
    public function createDraft($request) {
                $this->deleteDraft($request);
                
		$sql = "INSERT INTO draft
				(topic_id, perso_id, user_id, content)
				VALUES
				(:topic,:perso,:user,:content)";

		$stmt = $this->db->prepare($sql);
		$stmt->bindValue("topic", $request->get('topic_id'));
		if($request->get('perso_id') != '') {
			$stmt->bindValue("perso", $request->get('perso_id'));
		} else {
			$stmt->bindValue("perso", null);
		}
		$stmt->bindValue("user", $this->session->get('user')['id']);
		$stmt->bindValue("content", $request->get('content'));
		$stmt->execute();
    }
    
    public function getDraft($topic_id, $user_id) {
		$sql = "SELECT * 
                        FROM draft
                        WHERE user_id = :user
                        AND topic_id = :topic";

                return $this->db->fetchAssoc($sql,
    			array("topic" => $topic_id, "user" => $user_id)
    		);
    }

    public function createDicerPost($topic_id, $content) {
    	$sql = "INSERT INTO posts
				(topic_id, content)
				VALUES
				(:topic, :content)";

    	$stmt = $this->db->prepare($sql);
    	$stmt->bindValue("topic", $topic_id);
    	$stmt->bindValue("content", $content);
    	$stmt->execute();

    	$sql = "SELECT max(id)
				FROM posts
				WHERE topic_id = :topic_id";
    	return $this->db->fetchColumn($sql, array("topic_id" => $topic_id), 0);
    }

    public function updatePost($request) {
    	$sql = "UPDATE posts
    			SET content = :content,
    			perso_id = :perso
    			WHERE
    				id = :id";

		$stmt = $this->db->prepare($sql);
		$stmt->bindValue("content", $request->get('content'));
		if ($request->get('perso_id') != "") {
			$stmt->bindValue("perso", $request->get('perso_id'));
		} else {
			$stmt->bindValue("perso", null);
		}
		$stmt->bindValue("id", $request->get('id'));
		$stmt->execute();
    }


    public function deletePost($post_id) {
    	$sql = "DELETE FROM posts
    			WHERE id = :id";

    	$stmt = $this->db->prepare($sql);
    	$stmt->bindValue("id", $post_id);
    	$stmt->execute();
    }

    public function markRead($last_id, $topic_id) {
    	$user_id =  $this->session->get('user')['id'];
    	$sql = "SELECT count(*)
    			FROM read_post
    			WHERE
    				topic_id = :topic
    			AND user_id = :user";


    	$nbUpdatedLine = $this->db->fetchColumn($sql, array('topic' => $topic_id, 'user' => $user_id),0);

    	if ($nbUpdatedLine > 0) {
    		$this->updateReadPost($last_id, $topic_id, $user_id);
    	} else {
    		$this->insertReadPost($last_id, $topic_id, $user_id);
    	}
    }


    public function updateReadPost($last_id, $topic_id, $user_id) {
    	$sql = "UPDATE read_post
    			SET post_id = :post
    			WHERE
    				topic_id = :topic
    			AND user_id = :user
    			AND post_id < :post";

    	$stmt = $this->db->prepare($sql);
    	$stmt->bindValue("post", $last_id);
    	$stmt->bindValue("topic", $topic_id);
    	$stmt->bindValue("user", $user_id);
    	$stmt->execute();
    }

    public function insertReadPost($last_id, $topic_id, $user_id) {
    	$sql = "INSERT INTO read_post
    			(post_id, topic_id, user_id)
    			VALUES
    			(:post, :topic, :user)";

    	$stmt = $this->db->prepare($sql);
    	$stmt->bindValue("post", $last_id);
    	$stmt->bindValue("topic", $topic_id);
    	$stmt->bindValue("user", $user_id);
    	$stmt->execute();
    }

}
?>
