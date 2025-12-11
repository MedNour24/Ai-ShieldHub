<?php

/**
 * Team Class - Database Operations
 * Manages CRUD operations for tournament teams
 */

class Team
{
    private $pdo;
    
    // Properties matching database schema
    private $id_team;
    private $id_tournoi;
    private $team_name;
    private $team_tag;
    private $country;
    private $leader_name;
    private $leader_email;
    private $leader_phone;
    private $category;
    private $members;
    private $created_at;

    /**
     * Constructor
     */
    public function __construct($pdo)
    {
        $this->pdo = $pdo;
        $this->id_team = null;
        $this->id_tournoi = 0;
        $this->team_name = '';
        $this->team_tag = '';
        $this->country = '';
        $this->leader_name = '';
        $this->leader_email = '';
        $this->leader_phone = '';
        $this->category = '';
        $this->members = [];
        $this->created_at = null;
    }

    // ============ GETTERS ============
    
    public function getIdTeam(): ?int
    {
        return $this->id_team;
    }

    public function getIdTournoi(): int
    {
        return $this->id_tournoi;
    }

    public function getTeamName(): string
    {
        return $this->team_name;
    }

    public function getTeamTag(): string
    {
        return $this->team_tag;
    }

    public function getCountry(): string
    {
        return $this->country;
    }

    public function getLeaderName(): string
    {
        return $this->leader_name;
    }

    public function getLeaderEmail(): string
    {
        return $this->leader_email;
    }

    public function getLeaderPhone(): string
    {
        return $this->leader_phone;
    }

    public function getCategory(): string
    {
        return $this->category;
    }

    public function getMembers(): array
    {
        return $this->members;
    }

    public function getCreatedAt(): ?string
    {
        return $this->created_at;
    }

    public function getTotalMembers(): int
    {
        return count($this->members) + 1; // +1 for leader
    }

    // ============ SETTERS ============
    
    public function setIdTeam(?int $id_team): void
    {
        $this->id_team = $id_team;
    }

    public function setIdTournoi(int $id_tournoi): void
    {
        $this->id_tournoi = $id_tournoi;
    }

    public function setTeamName(string $team_name): void
    {
        $this->team_name = trim($team_name);
    }

    public function setTeamTag(string $team_tag): void
    {
        $this->team_tag = strtoupper(trim($team_tag));
    }

    public function setCountry(string $country): void
    {
        $this->country = trim($country);
    }

    public function setLeaderName(string $leader_name): void
    {
        $this->leader_name = trim($leader_name);
    }

    public function setLeaderEmail(string $leader_email): void
    {
        $this->leader_email = trim($leader_email);
    }

    public function setLeaderPhone(string $leader_phone): void
    {
        $this->leader_phone = trim($leader_phone);
    }

    public function setCategory(string $category): void
    {
        $this->category = trim($category);
    }

    public function setMembers(array $members): void
    {
        $this->members = $members;
    }

    public function setCreatedAt(?string $created_at): void
    {
        $this->created_at = $created_at;
    }

    // ============ CRUD METHODS ============

    /**
     * Create a new team
     */
    public function create(): bool
    {
        try {
            $sql = "INSERT INTO teams (
                id_tournoi,
                team_name,
                team_tag,
                country,
                leader_name,
                leader_email,
                leader_phone,
                category,
                members,
                created_at
            ) VALUES (
                :id_tournoi,
                :team_name,
                :team_tag,
                :country,
                :leader_name,
                :leader_email,
                :leader_phone,
                :category,
                :members,
                NOW()
            )";

            $stmt = $this->pdo->prepare($sql);
            $members_json = json_encode($this->members, JSON_UNESCAPED_UNICODE);

            $stmt->bindValue(':id_tournoi', $this->id_tournoi, PDO::PARAM_INT);
            $stmt->bindValue(':team_name', $this->team_name, PDO::PARAM_STR);
            $stmt->bindValue(':team_tag', $this->team_tag, PDO::PARAM_STR);
            $stmt->bindValue(':country', $this->country, PDO::PARAM_STR);
            $stmt->bindValue(':leader_name', $this->leader_name, PDO::PARAM_STR);
            $stmt->bindValue(':leader_email', $this->leader_email, PDO::PARAM_STR);
            $stmt->bindValue(':leader_phone', $this->leader_phone, PDO::PARAM_STR);
            $stmt->bindValue(':category', $this->category, PDO::PARAM_STR);
            $stmt->bindValue(':members', $members_json, PDO::PARAM_STR);

            $result = $stmt->execute();
            
            if ($result) {
                $this->id_team = (int)$this->pdo->lastInsertId();
            }

            return $result;
        } catch (PDOException $e) {
            error_log("Error creating team: " . $e->getMessage());
            throw new Exception("Erreur lors de la création de l'équipe");
        }
    }

    /**
     * Read a team by ID
     */
    public function read(int $id_team): bool
    {
        try {
            $sql = "SELECT * FROM teams WHERE id_team = :id_team";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':id_team', $id_team, PDO::PARAM_INT);
            $stmt->execute();

            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($row) {
                $this->id_team = (int)$row['id_team'];
                $this->id_tournoi = (int)$row['id_tournoi'];
                $this->team_name = $row['team_name'];
                $this->team_tag = $row['team_tag'];
                $this->country = $row['country'];
                $this->leader_name = $row['leader_name'];
                $this->leader_email = $row['leader_email'];
                $this->leader_phone = $row['leader_phone'];
                $this->category = $row['category'];
                $this->members = json_decode($row['members'], true) ?: [];
                $this->created_at = $row['created_at'];
                return true;
            }

            return false;
        } catch (PDOException $e) {
            error_log("Error reading team: " . $e->getMessage());
            throw new Exception("Erreur lors de la lecture de l'équipe");
        }
    }

    /**
     * Update a team
     */
    public function update(): bool
    {
        try {
            $sql = "UPDATE teams SET
                id_tournoi = :id_tournoi,
                team_name = :team_name,
                team_tag = :team_tag,
                country = :country,
                leader_name = :leader_name,
                leader_email = :leader_email,
                leader_phone = :leader_phone,
                category = :category,
                members = :members
            WHERE id_team = :id_team";

            $stmt = $this->pdo->prepare($sql);
            $members_json = json_encode($this->members, JSON_UNESCAPED_UNICODE);

            $stmt->bindValue(':id_team', $this->id_team, PDO::PARAM_INT);
            $stmt->bindValue(':id_tournoi', $this->id_tournoi, PDO::PARAM_INT);
            $stmt->bindValue(':team_name', $this->team_name, PDO::PARAM_STR);
            $stmt->bindValue(':team_tag', $this->team_tag, PDO::PARAM_STR);
            $stmt->bindValue(':country', $this->country, PDO::PARAM_STR);
            $stmt->bindValue(':leader_name', $this->leader_name, PDO::PARAM_STR);
            $stmt->bindValue(':leader_email', $this->leader_email, PDO::PARAM_STR);
            $stmt->bindValue(':leader_phone', $this->leader_phone, PDO::PARAM_STR);
            $stmt->bindValue(':category', $this->category, PDO::PARAM_STR);
            $stmt->bindValue(':members', $members_json, PDO::PARAM_STR);

            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error updating team: " . $e->getMessage());
            throw new Exception("Erreur lors de la mise à jour de l'équipe");
        }
    }

    /**
     * Delete a team
     */
    public function delete(int $id_team): bool
    {
        try {
            $sql = "DELETE FROM teams WHERE id_team = :id_team";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':id_team', $id_team, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error deleting team: " . $e->getMessage());
            throw new Exception("Erreur lors de la suppression de l'équipe");
        }
    }

    /**
     * List all teams with tournament info
     */
    public function listAll(): array
    {
        try {
            $sql = "SELECT t.*,
                tr.nom AS nom_tournoi,
                tr.date_debut,
                tr.date_fin,
                tr.theme,
                tr.niveau
            FROM teams t
            LEFT JOIN tournoi tr ON t.id_tournoi = tr.id
            ORDER BY t.created_at DESC";

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error listing teams: " . $e->getMessage());
            return [];
        }
    }

    /**
     * List teams by tournament
     */
    public function listByTournoi(int $id_tournoi): array
    {
        try {
            $sql = "SELECT t.*,
                tr.nom AS nom_tournoi,
                tr.date_debut,
                tr.date_fin,
                tr.theme,
                tr.niveau
            FROM teams t
            LEFT JOIN tournoi tr ON t.id_tournoi = tr.id
            WHERE t.id_tournoi = :id_tournoi
            ORDER BY t.created_at DESC";

            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':id_tournoi', $id_tournoi, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error listing teams by tournament: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Filter teams by category
     */
    public function listByCategory(string $category): array
    {
        try {
            $sql = "SELECT t.*,
                tr.nom AS nom_tournoi,
                tr.date_debut,
                tr.date_fin,
                tr.theme,
                tr.niveau
            FROM teams t
            LEFT JOIN tournoi tr ON t.id_tournoi = tr.id
            WHERE t.category = :category
            ORDER BY t.created_at DESC";

            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':category', $category, PDO::PARAM_STR);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error listing teams by category: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Search teams by name or tag
     */
    public function search(string $keyword): array
    {
        try {
            $sql = "SELECT t.*,
                tr.nom AS nom_tournoi,
                tr.date_debut,
                tr.date_fin,
                tr.theme,
                tr.niveau
            FROM teams t
            LEFT JOIN tournoi tr ON t.id_tournoi = tr.id
            WHERE t.team_name LIKE :keyword 
                OR t.team_tag LIKE :keyword
                OR t.leader_name LIKE :keyword
            ORDER BY t.created_at DESC";

            $stmt = $this->pdo->prepare($sql);
            $searchTerm = '%' . $keyword . '%';
            $stmt->bindValue(':keyword', $searchTerm, PDO::PARAM_STR);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error searching teams: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Join a team (add a member)
     */
    public function joinTeam(int $id_team, array $memberData): bool
    {
        try {
            // Read current team data
            if (!$this->read($id_team)) {
                throw new Exception("Équipe introuvable");
            }

            // Check if team is full (max 5 members including leader)
            if (count($this->members) >= 4) {
                throw new Exception("L'équipe est complète (maximum 5 membres)");
            }

            // Check if email already exists in team
            foreach ($this->members as $member) {
                if ($member['email'] === $memberData['email']) {
                    throw new Exception("Cet email est déjà utilisé dans cette équipe");
                }
            }

            // Check if leader email matches
            if ($this->leader_email === $memberData['email']) {
                throw new Exception("Cet email appartient au leader de l'équipe");
            }

            // Add new member
            $this->members[] = $memberData;

            // Update team
            $sql = "UPDATE teams SET members = :members WHERE id_team = :id_team";
            $stmt = $this->pdo->prepare($sql);
            $members_json = json_encode($this->members, JSON_UNESCAPED_UNICODE);
            $stmt->bindValue(':members', $members_json, PDO::PARAM_STR);
            $stmt->bindValue(':id_team', $id_team, PDO::PARAM_INT);

            return $stmt->execute();
        } catch (Exception $e) {
            error_log("Error joining team: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get available tournaments - FIXED VERSION
     */
    public function getAvailableTournois(): array
    {
        try {
            $sql = "SELECT 
                id AS id_tournoi, 
                nom AS nom_tournoi, 
                date_debut, 
                date_fin,
                theme,
                niveau,
                description
            FROM tournoi
            WHERE date_fin >= CURDATE()
            ORDER BY date_debut ASC";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Log for debugging
            error_log("Tournaments found: " . count($result));
            
            return $result;
        } catch (PDOException $e) {
            error_log("Error getting available tournaments: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Check if team tag is unique
     */
    public function isTagUnique(string $tag, ?int $exclude_id = null): bool
    {
        try {
            $sql = "SELECT COUNT(*) FROM teams WHERE team_tag = :tag";
            if ($exclude_id !== null) {
                $sql .= " AND id_team != :exclude_id";
            }

            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':tag', strtoupper($tag), PDO::PARAM_STR);
            if ($exclude_id !== null) {
                $stmt->bindValue(':exclude_id', $exclude_id, PDO::PARAM_INT);
            }

            $stmt->execute();
            return $stmt->fetchColumn() == 0;
        } catch (PDOException $e) {
            error_log("Error checking tag uniqueness: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get team statistics
     */
    public function getStatistics(): array
    {
        try {
            $stats = [
                'total_teams' => 0,
                'total_members' => 0,
                'full_teams' => 0,
                'by_category' => []
            ];

            // Total teams
            $sql = "SELECT COUNT(*) FROM teams";
            $stmt = $this->pdo->query($sql);
            $stats['total_teams'] = (int)$stmt->fetchColumn();

            // Teams by category
            $sql = "SELECT category, COUNT(*) as count FROM teams GROUP BY category";
            $stmt = $this->pdo->query($sql);
            $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($categories as $cat) {
                $stats['by_category'][$cat['category']] = (int)$cat['count'];
            }

            // Total members (leaders + members)
            $sql = "SELECT members FROM teams";
            $stmt = $this->pdo->query($sql);
            $rows = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            $memberCount = $stats['total_teams']; // Count leaders
            $fullTeamCount = 0;
            
            foreach ($rows as $membersJson) {
                $members = json_decode($membersJson, true) ?: [];
                $memberCount += count($members);
                
                // Check if team is full (4 members + 1 leader = 5)
                if (count($members) >= 4) {
                    $fullTeamCount++;
                }
            }
            
            $stats['total_members'] = $memberCount;
            $stats['full_teams'] = $fullTeamCount;
            $stats['average_members'] = $stats['total_teams'] > 0 
                ? round($memberCount / $stats['total_teams'], 2) 
                : 0;

            return $stats;
        } catch (PDOException $e) {
            error_log("Error getting statistics: " . $e->getMessage());
            return [
                'total_teams' => 0,
                'total_members' => 0,
                'full_teams' => 0,
                'by_category' => [],
                'average_members' => 0
            ];
        }
    }

    /**
     * Check if a member can join (validates business rules)
     */
    public function canJoinTeam(int $id_team, string $email): array
    {
        try {
            if (!$this->read($id_team)) {
                return [
                    'can_join' => false,
                    'reason' => 'Équipe introuvable'
                ];
            }

            // Check if team is full
            if (count($this->members) >= 4) {
                return [
                    'can_join' => false,
                    'reason' => 'L\'équipe est complète'
                ];
            }

            // Check if email exists
            if ($this->leader_email === $email) {
                return [
                    'can_join' => false,
                    'reason' => 'Vous êtes déjà le leader de cette équipe'
                ];
            }

            foreach ($this->members as $member) {
                if ($member['email'] === $email) {
                    return [
                        'can_join' => false,
                        'reason' => 'Vous êtes déjà membre de cette équipe'
                    ];
                }
            }

            return [
                'can_join' => true,
                'available_slots' => 4 - count($this->members)
            ];
        } catch (Exception $e) {
            error_log("Error checking join eligibility: " . $e->getMessage());
            return [
                'can_join' => false,
                'reason' => 'Erreur lors de la vérification'
            ];
        }
    }
}
?>