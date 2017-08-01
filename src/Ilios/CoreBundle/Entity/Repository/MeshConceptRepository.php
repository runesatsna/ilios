<?php
namespace Ilios\CoreBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\AbstractQuery;
use Ilios\CoreBundle\Entity\DTO\MeshConceptDTO;

/**
 * Class MeshConceptRepository
 */
class MeshConceptRepository extends EntityRepository implements DTORepositoryInterface
{
    /**
     * @inheritdoc
     */
    public function findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('DISTINCT x')->from('IliosCoreBundle:MeshConcept', 'x');

        $this->attachCriteriaToQueryBuilder($qb, $criteria, $orderBy, $limit, $offset);

        return $qb->getQuery()->getResult();
    }

    /**
     * Find and hydrate as DTOs
     *
     * @param array $criteria
     * @param array|null $orderBy
     * @param null $limit
     * @param null $offset
     *
     * @return array
     */
    public function findDTOsBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
    {
        $qb = $this->_em->createQueryBuilder()->select('x')
            ->distinct()->from('IliosCoreBundle:MeshConcept', 'x');
        $this->attachCriteriaToQueryBuilder($qb, $criteria, $orderBy, $limit, $offset);

        /** @var MeshConceptDTO[] $meshConceptDTOs */
        $meshConceptDTOs = [];
        foreach ($qb->getQuery()->getResult(AbstractQuery::HYDRATE_ARRAY) as $arr) {
            $meshConceptDTOs[$arr['id']] = new MeshConceptDTO(
                $arr['id'],
                $arr['name'],
                $arr['umlsUid'],
                $arr['preferred'],
                $arr['scopeNote'],
                $arr['casn1Name'],
                $arr['registryNumber'],
                $arr['createdAt'],
                $arr['updatedAt']
            );
        }
        $meshConceptIds = array_keys($meshConceptDTOs);

        $related = [
            'semanticTypes',
            'terms',
            'descriptors',
        ];
        foreach ($related as $rel) {
            $qb = $this->_em->createQueryBuilder()
                ->select('r.id AS relId, x.id AS meshConceptId')
                ->from('IliosCoreBundle:MeshConcept', 'x')
                ->join("x.{$rel}", 'r')
                ->where($qb->expr()->in('x.id', ':ids'))
                ->orderBy('relId')
                ->setParameter('ids', $meshConceptIds);
            foreach ($qb->getQuery()->getResult() as $arr) {
                $meshConceptDTOs[$arr['meshConceptId']]->{$rel}[] = $arr['relId'];
            }
        }
        return array_values($meshConceptDTOs);
    }


    /**
     * @param QueryBuilder $qb
     * @param array $criteria
     * @param array $orderBy
     * @param int $limit
     * @param int $offset
     *
     * @return QueryBuilder
     */
    protected function attachCriteriaToQueryBuilder(QueryBuilder $qb, $criteria, $orderBy, $limit, $offset)
    {
        $related = [
            'semanticTypes',
            'terms',
            'descriptors',
        ];
        foreach ($related as $rel) {
            if (array_key_exists($rel, $criteria)) {
                $ids = is_array($criteria[$rel]) ?
                    $criteria[$rel] : [$criteria[$rel]];
                $alias = "alias_${rel}";
                $param = ":${rel}";
                $qb->join("x.${rel}", $alias);
                $qb->andWhere($qb->expr()->in("${alias}.id", $param));
                $qb->setParameter($param, $ids);
            }
            unset($criteria[$rel]);
        }

        if (count($criteria)) {
            foreach ($criteria as $key => $value) {
                $values = is_array($value) ? $value : [$value];
                $qb->andWhere($qb->expr()->in("x.{$key}", ":{$key}"));
                $qb->setParameter(":{$key}", $values);
            }
        }

        if (empty($orderBy)) {
            $orderBy = ['id' => 'ASC'];
        }

        if (is_array($orderBy)) {
            foreach ($orderBy as $sort => $order) {
                $qb->addOrderBy('x.'.$sort, $order);
            }
        }

        if ($offset) {
            $qb->setFirstResult($offset);
        }

        if ($limit) {
            $qb->setMaxResults($limit);
        }

        return $qb;
    }
}
