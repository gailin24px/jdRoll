<?php

class CampagneService {

	private $db;
	private $session;
	private $persoService;
	private $userService;

	public function __construct($db, $session,$persoService, $userService)
    {
        $this->db = $db;
        $this->session = $session;
        $this->persoService = $persoService;
        $this->userService = $userService;
    }

    public function getBlankCampagne() {
		$campagne = array();
		$campagne['id'] = '';
		$campagne['nb_joueurs'] = '4';
		$campagne['name'] = '';
		$campagne['banniere'] = '';
		$campagne['systeme'] = '';
		$campagne['univers'] = '';
		$campagne['description'] = '';
		$campagne['statut'] = 3;
		return $campagne;
    }

    public function getFormCampagne($request) {
		$campagne = array();
		$campagne['id'] = $request->get('id');
		$campagne['nb_joueurs'] = $request->get('nb_joueurs');
		$campagne['name'] = $request->get('name');
		$campagne['banniere'] = $request->get('banniere');
		$campagne['systeme'] = $request->get('systeme');
		$campagne['univers'] = $request->get('univers');
		$campagne['description'] = $request->get('description');
		$campagne['statut'] = $request->get('statut');
		return $campagne;
    }
    
    public function getFormCampagneConfig($request) {
    	$campagne = array();
    	$campagne['campagne_id'] = $request->get('campagne_id');
    	$campagne['banniere'] = $request->get('banniere');
    	$campagne['hr'] = $request->get('hr');
    	$campagne['odd_line_color'] = $request->get('odd_line_color');
    	$campagne['even_line_color'] = $request->get('even_line_color');
    	$campagne['sidebar_color'] = $request->get('sidebar_color');
    	$campagne['link_color'] = $request->get('link_color');
    	$campagne['template'] = $request->get('template');
    	$campagne['sidebar_text'] = $request->get('sidebar_text');
    	return $campagne;
    }

    public function createCampagne($request) {	
		$sql = "INSERT INTO campagne 
				(name, systeme, univers, nb_joueurs, description, mj_id, banniere, statut) 
				VALUES
				(:name,:systeme,:univers,:nb_joueurs,:description,:mj_id,:banniere, :statut)";

		$stmt = $this->db->prepare($sql);
		$stmt->bindValue("name", $request->get('name'));
		$stmt->bindValue("banniere", $request->get('banniere'));
		$stmt->bindValue("systeme", $request->get('systeme'));
		$stmt->bindValue("univers", $request->get('univers'));
		$stmt->bindValue("nb_joueurs", $request->get('nb_joueurs'));
		$stmt->bindValue("description", $request->get('description'));
		$stmt->bindValue("statut", $request->get('statut'));
		$stmt->bindValue("mj_id", $this->session->get('user')['id']);
		$stmt->execute();
		
		return $this->db->lastInsertId();
    }
    
    public function createCampagneConfig($campagne) {
    	$sql = "INSERT INTO campagne_config
				(campagne_id, banniere, hr, odd_line_color, even_line_color, sidebar_color, link_color, template, sidebar_text)
				VALUES
				(:campagne, '', '', '', '', '', '', '', '')";
    
    	$stmt = $this->db->prepare($sql);
    	$stmt->bindValue("campagne", $campagne);
    	$stmt->execute();
    }
    
    public function updateCampagneConfig($request) {
    	$sql = "UPDATE campagne_config
    			SET
    			banniere = :banniere,
    			hr = :hr,
    			odd_line_color = :odd_line_color,
    			even_line_color = :even_line_color,
    			sidebar_color = :sidebar_color,
    			link_color = :link_color,
    			template = :template,
    			sidebar_text = :sidebar_text
    			WHERE
    			campagne_id = :campagne";
    
    	$stmt = $this->db->prepare($sql);
    	$stmt->bindValue("campagne", $request->get('campagne_id'));
    	$stmt->bindValue("banniere", $request->get('banniere'));
    	$stmt->bindValue("hr", $request->get('hr'));
    	$stmt->bindValue("odd_line_color", $request->get('odd_line_color'));
    	$stmt->bindValue("even_line_color", $request->get('even_line_color'));
    	$stmt->bindValue("sidebar_color", $request->get('sidebar_color'));
    	$stmt->bindValue("link_color", $request->get('link_color'));
    	$stmt->bindValue("template", $request->get('template'));
    	$stmt->bindValue("sidebar_text", $request->get('sidebar_text'));
    	$stmt->execute();
    }

    public function updateCampagne($request) {
    	$sql = "UPDATE campagne 
    			SET name = :name,
    				banniere = :banniere,
    				systeme = :systeme,
    				univers = :univers,
    				nb_joueurs = :nb_joueurs,
    				description = :description,
    				statut = :statut
    			WHERE
    				id = :id";

		$stmt = $this->db->prepare($sql);
		$stmt->bindValue("name", $request->get('name'));
		$stmt->bindValue("banniere", $request->get('banniere'));
		$stmt->bindValue("systeme", $request->get('systeme'));
		$stmt->bindValue("univers", $request->get('univers'));
		$stmt->bindValue("statut", $request->get('statut'));
		$stmt->bindValue("nb_joueurs", $request->get('nb_joueurs'));
		$stmt->bindValue("description", $request->get('description'));
		$stmt->bindValue("id", $request->get('id'));
		$stmt->execute();
    }
    
    
    public function openSubscribe($id) {
    	$sql = "UPDATE campagne 
    			SET is_recrutement_open = :is_recrutement_open
    			WHERE
    				id = :id";

		$stmt = $this->db->prepare($sql);
		$stmt->bindValue("is_recrutement_open", 1);
		$stmt->bindValue("id", $id);
		$stmt->execute();
    }
    
    
    public function closeSubscribe($id) {
    	$sql = "UPDATE campagne 
    			SET is_recrutement_open = :is_recrutement_open
    			WHERE
    				id = :id";

		$stmt = $this->db->prepare($sql);
		$stmt->bindValue("is_recrutement_open", 0);
		$stmt->bindValue("id", $id);
		$stmt->execute();
    }

   	public function getAllCampagne() {
		$sql = "SELECT campagne.*, user.username as username
				FROM campagne 
				JOIN user 
				ON user.id = campagne.mj_id 
				WHERE campagne.statut < 3
				ORDER BY campagne.statut ASC, campagne.name ASC";
	    $campagnes = $this->db->fetchAll($sql);
	    return $campagnes;
	}

	
	public function getLastCampagne() {
		$sql = "SELECT campagne.*, user.username as username
				FROM campagne
				JOIN user ON user.id = campagne.mj_id
				WHERE campagne.STATUT = 0
				ORDER BY campagne.id desc
				LIMIT 0, 5";
		$campagnes = $this->db->fetchAll($sql);
		return $campagnes;
	}
	
	public function getOpenCampagne() {
		$sql = "SELECT campagne.*, user.username as username
				FROM campagne
				JOIN user ON user.id = campagne.mj_id
				WHERE campagne.STATUT < 2
				AND is_recrutement_open = 1
				ORDER BY campagne.name ASC";
		$campagnes = $this->db->fetchAll($sql);
		return $campagnes;
	}

	public function getCampagne($id) {
		$sql = "SELECT * FROM campagne WHERE id = ?";
	    $campagne = $this->db->fetchAssoc($sql, array($id));
	    return $campagne;
	}
	
	public function getCampagneConfig($id) {
		$sql = "SELECT * FROM campagne_config WHERE campagne_id = ?";
		$campagne = $this->db->fetchAssoc($sql, array($id));
		return $campagne;
	}
	
	public function isMj($id) {
		if($this->session->get('user') != null) {
			if($id == null) {
				return $this->userService->getCurrentUser()['profil'] > 0;
			} else {
				$campagne = $this->getCampagne($id);
				return $campagne['mj_id'] == $this->session->get('user')['id'];
			}
		} else {
			return false;
		}
	}
	
	public function isParticipant($id) {
		if($this->session->get('user') != null) {
			$sql = "SELECT user_id 
					FROM campagne_participant
					WHERE 
					campagne_id = :campagne
					AND user_id = :user";
			$result = $this->db->fetchColumn($sql, array('user' => $this->session->get('user')['id'], 'campagne' => $id ), 0);
			return ($result != null);
		} else {
			return false;
		}
	}
        
       
        public function isRealJoueur($campagne_id, $userid) {
		if($this->session->get('user') != null) {
			$sql = "SELECT user_id 
					FROM campagne_participant
					WHERE 
					campagne_id = :campagne
					AND user_id = :user
                                        AND statut = 1";
			$result = $this->db->fetchColumn($sql, array('user' => $user_id, 'campagne' => $campagne_id ), 0);
			return ($result != null);
		} else {
			return false;
		}
	}

	public function getMyActiveMjCampagnes() {
		return $this->getActiveMjCampagnes($this->session->get('user')['id']);
	}
	
	
	public function getActiveMjCampagnes($id) {
		$sql = "SELECT *
				FROM campagne
				WHERE mj_id = :user 
				AND statut IN (0, 3)
				ORDER BY name";
	    $campagne = $this->db->fetchAll($sql, array('user' => $id));
	    return $campagne;
	}

	public function getMyActivePjCampagnes() {
		return $this->getActivePjCampagnes($this->session->get('user')['id']);
	}
	
	public function getActivePjCampagnes($id) {
		$sql = "SELECT
		campagne.*, user.username as username
		FROM campagne
		JOIN campagne_participant as cp
		ON cp.campagne_id = campagne.id
		JOIN user ON user.id = campagne.mj_id
		WHERE cp.user_id = ?
		AND campagne.statut = 0
		ORDER BY campagne.name";
		$campagne = $this->db->fetchAll($sql, array($id));
		return $campagne;
	}
	
	public function getMyCampagnes() {
		$sql = "SELECT * ,
				( SELECT
					max((IFNULL(topics.last_post_id, 0) - IFNULL(read_post.post_id, 0)))
					FROM
					sections
					JOIN topics
					ON sections.id = topics.section_id
					LEFT JOIN read_post
					ON read_post.topic_id = topics.id
					AND read_post.user_id = :user
					WHERE
					sections.campagne_id = campagne.id
				) as activity
				FROM campagne
				WHERE mj_id = :user
				AND campagne.statut <> 2
				ORDER BY name";
		$campagne = $this->db->fetchAll($sql, array('user' => $this->session->get('user')['id']));
		return $campagne;
	}
        
        public function getMyCampagnesWithWaiting() {
		$sql = "SELECT campagne.id, campagne.name 
			FROM campagne
                        JOIN campagne_participant cp
                        ON campagne.id = campagne_id
			WHERE mj_id = :user
			AND campagne.statut <> 2
                        AND cp.statut = 0
			ORDER BY name";
		$campagne = $this->db->fetchAll($sql, array('user' => $this->session->get('user')['id']));
		return $campagne;
	}
	
	public function getMyPjCampagnes() {
            return $this->getMyPjCampagneByStatut(1);
	}
        
        public function getMyWaitingPjCampagnes() {
            return $this->getMyPjCampagneByStatut(0);
	}
        
        
        public function getMyPjCampagneByStatut($statut) {
            $sql = "SELECT
		campagne.*, user.username as username,
				( SELECT 
					max((IFNULL(topics.last_post_id, 0) - IFNULL(read_post.post_id, 0)))
					FROM 
					sections
					JOIN topics
					ON sections.id = topics.section_id
					LEFT JOIN read_post
					ON read_post.topic_id = topics.id
					AND read_post.user_id = :user
					LEFT JOIN can_read
					ON can_read.topic_id = topics.id
					AND can_read.user_id = :user
					WHERE
					sections.campagne_id = campagne.id 
					AND (
						(topics.is_private <> 1)
						OR
						(campagne.mj_id = :user)
						OR
						(can_read.topic_id IS NOT NULL)
					)
				) as activity			
		FROM campagne
		JOIN campagne_participant as cp
		ON cp.campagne_id = campagne.id
		JOIN user ON user.id = campagne.mj_id
		WHERE cp.user_id = :user
		AND campagne.statut <> 2
                AND cp.statut = :statut
		ORDER BY campagne.name";
		$campagne = $this->db->fetchAll($sql, array('user' => $this->session->get('user')['id'], 'statut' => $statut));
		return $campagne;
        }

	public function getMyMjArchiveCampagnes() {
		$sql = "SELECT * ,
				( SELECT
					max((IFNULL(topics.last_post_id, 0) - IFNULL(read_post.post_id, 0)))
					FROM
					sections
					JOIN topics
					ON sections.id = topics.section_id
					LEFT JOIN read_post
					ON read_post.topic_id = topics.id
					AND read_post.user_id = :user
					LEFT JOIN can_read
					ON can_read.topic_id = topics.id
					AND can_read.user_id = :user
					WHERE
					sections.campagne_id = campagne.id
					AND (
						(topics.is_private <> 1)
						OR
						(campagne.mj_id = :user)
						OR
						(can_read.topic_id IS NOT NULL)
					)
				) as activity
				FROM campagne
				WHERE mj_id = :user
				AND statut = 2
				ORDER BY name";
		$campagne = $this->db->fetchAll($sql, array('user' => $this->session->get('user')['id']));
		return $campagne;
	}
	
	public function getMyPjArchiveCampagnes() {
		$sql = "SELECT
		campagne.*, user.username as username,
				( SELECT
					max((IFNULL(topics.last_post_id, 0) - IFNULL(read_post.post_id, 0)))
					FROM
					sections
					JOIN topics
					ON sections.id = topics.section_id
					LEFT JOIN read_post
					ON read_post.topic_id = topics.id
					AND read_post.user_id = :user
					LEFT JOIN can_read
					ON can_read.topic_id = topics.id
					AND can_read.user_id = :user
					WHERE
					sections.campagne_id = campagne.id
					AND (
						(topics.is_private <> 1)
						OR
						(campagne.mj_id = :user)
						OR
						(can_read.topic_id IS NOT NULL)
					)
				) as activity
		FROM campagne
		JOIN campagne_participant as cp
		ON cp.campagne_id = campagne.id
		JOIN user ON user.id = campagne.mj_id
		WHERE cp.user_id = :user
		AND campagne.statut = 2
		ORDER BY campagne.name";
		$campagne = $this->db->fetchAll($sql, array('user' => $this->session->get('user')['id']));
		return $campagne;
	}
	
	private function incrementeNbJoueur($id) {
		$sql = "UPDATE campagne 
				SET 
					nb_joueurs_actuel = nb_joueurs_actuel + 1
                                AND     is_recrutement_open = IF(nb_joueurs_actuel = nb_joueurs, 0, 1)
				WHERE id = :id";

		$stmt = $this->db->prepare($sql);
		$stmt->bindValue("id", $id);
		$stmt->execute();
	}

	private function decrementeNbJoueur($id) {
		$sql = "UPDATE campagne 
				SET 
					nb_joueurs_actuel = nb_joueurs_actuel - 1
				WHERE id = :id";

		$stmt = $this->db->prepare($sql);
		$stmt->bindValue("id", $id);
		$stmt->execute();
	}
	
	public function getParticipant($campagne_id) {
		$sql = "SELECT user.*, cp.statut as part_statut
				FROM campagne_participant cp
				JOIN
				user
				ON user.id = cp.user_id
				WHERE
				cp.campagne_id = :campagne";
		return $this->db->fetchAll($sql, array('campagne' => $campagne_id));
	}

	private function insertParticipant($campagne_id, $user_id) {
		$sql = "INSERT INTO campagne_participant 
				(campagne_id, user_id) 
				VALUES
				(:campagne, :user)";

		$stmt = $this->db->prepare($sql);
		$stmt->bindValue("campagne", $campagne_id);
		$stmt->bindValue("user", $user_id);
		$stmt->execute();
	}

	private function deleteParticipant($campagne_id, $user_id) {
		$sql = "DELETE FROM campagne_participant 
				WHERE
				campagne_id = :campagne
				AND user_id = :user";

		$stmt = $this->db->prepare($sql);
		$stmt->bindValue("campagne", $campagne_id);
		$stmt->bindValue("user", $user_id);
		$stmt->execute();
	}

	private function createPersonnage($campagne_id, $user_id) {
		$sql = "INSERT INTO campagne_participant 
				(campagne_id, user_id) 
				VALUES
				(:campagne, :user)";

		$stmt = $this->db->prepare($sql);
		$stmt->bindValue("campagne", $campagne_id);
		$stmt->bindValue("user", $user_id);
		$stmt->execute();
	}

	private function checkIfNotParticipant($campagne_id, $user_id) {
		$sql = "SELECT count(*) FROM campagne_participant 
				WHERE campagne_id = :campagne
				AND   user_id = :user";

		$stmt = $this->db->prepare($sql);
		$stmt->bindValue("campagne", $campagne_id);
		$stmt->bindValue("user", $user_id);
		$stmt->execute();

		$res = $stmt->fetchColumn(0);
		if($res > 0) {
			throw new Exception("Vous êtes déjà inscrit");
		}
	}

	public function addJoueur($campagne_id, $user_id) {
		$campagne = $this->getCampagne($campagne_id);
		if($campagne['nb_joueurs'] <= $campagne['nb_joueurs_actuel']) {
			throw new Exception("La partie est déjà complète");
		}
		$this->checkIfNotParticipant($campagne_id, $user_id);
		$this->insertParticipant($campagne_id, $user_id);
	}
        
        public function validJoueur($campagne_id, $user_id) {
            $sql = "UPDATE campagne_participant
                    SET statut = 1
                    WHERE campagne_id = :campagne
                    AND user_id = :user";
            
            $this->incrementeNbJoueur($campagne_id);
            $stmt = $this->db->prepare($sql);
		$stmt->bindValue("campagne", $campagne_id);
		$stmt->bindValue("user", $user_id);
		$stmt->execute();
        }

	public function removeJoueur($campagne_id, $user_id) {
		try {
			$this->checkIfNotParticipant($campagne_id, $user_id);
			throw new Exception("Vous n'êtes pas inscrit à cette partie.");
		} catch (Exception $e) {
			$this->deleteParticipant($campagne_id, $user_id);
                        if($this->isRealJoueur($campagne_id, $userid)) {
                            $this->decrementeNbJoueur($campagne_id);
                            $this->persoService->detachPersonnage($campagne_id, $user_id);
                        }
		}	
	}
}
?>