<?php
$file = __DIR__ . '/src/App/UserdirectoryBundle/Repository/UserRepository.php';
$content = file_get_contents($file);

$pattern = '/    \/\/\$roles: role or partial role name\s*\n    public function findUsersByRoles\(\$roles\) \{.*?\n    \}/s';

$replacement = <<<'PHP'
    //$roles: role or partial role name
    public function findUsersByRoles($roles) {

        //JSON roles column: use PostgreSQL jsonb containment instead of LIKE.
        $connection = $this->_em->getConnection();
        $conditions = array();
        $params = array();
        $i = 0;
        foreach ($roles as $role) {
            $conditions[] = "roles::jsonb @> :role$i::jsonb";
            $params["role$i"] = json_encode(array($role));
            $i++;
        }

        $where = implode(' OR ', $conditions);
        $sql = "SELECT id FROM user_fosuser WHERE $where";
        $rows = $connection->executeQuery($sql, $params)->fetchAllAssociative();

        $ids = array_column($rows, 'id');
        if (empty($ids)) {
            return array();
        }

        return $this->_em->createQueryBuilder()
            ->select('u')
            ->from(User::class, 'u')
            ->where('u.id IN (:ids)')
            ->setParameter('ids', $ids)
            ->getQuery()
            ->getResult();
    }
PHP;

$newContent = preg_replace($pattern, $replacement, $content);
if ($newContent === $content) {
    echo "No replacement made\n";
    exit(1);
}
file_put_contents($file, $newContent);
echo "Updated findUsersByRoles\n";
