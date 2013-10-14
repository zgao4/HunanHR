<?php
namespace HR\Bundle\JobBundle\EntityManager;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\NoResultException;
use HR\Bundle\JobBundle\Model\JobInterface;
use  HR\Bundle\JobBundle\ModelManager\JobManager as BaseJobManager;
use HR\Bundle\JobBundle\Pagination\Pager;
use HR\Bundle\JobBundle\Pagination\ProxyQuery;
use HR\Bundle\UserBundle\Model\UserInterface;

/**
 * @author Wenming Tang <tang@babyfamily.com>
 */
class JobManager extends BaseJobManager
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $em;

    /**
     * @var \Doctrine\ORM\EntityRepository
     */
    protected $repository;

    /**
     * @var string
     */
    protected $class;

    public function __construct(EntityManager $em, $class)
    {
        $this->em         = $em;
        $this->repository = $this->em->getRepository($class);
        $this->class      = $this->em->getClassMetadata($class)->getName();
    }

    public function findJobsPagerByUser(UserInterface $user, $page = 1)
    {
        $qb = $this->repository->createQueryBuilder('j')
            ->select('j, u, c')
            ->join('j.user', 'u')
            ->join('j.city', 'c')
            ->where('u.id = :user')
            ->addOrderBy('j.createdAt', 'DESC')
            ->setParameter('user', $user->getId());

        $pager = new Pager(new ProxyQuery($qb));
        $pager->setPage($page);

        return $pager;
    }

    public function findJobsPagerByLatest($page = 1)
    {
        $qb = $this->repository->createQueryBuilder('j')
            ->select('j, u, c')
            ->join('j.user', 'u')
            ->join('j.city', 'c')
            ->addOrderBy('j.createdAt', 'DESC');

        $pager = new Pager(new ProxyQuery($qb));
        $pager->setPage($page);

        return $pager;
    }

    public function findJobById($id)
    {
        $q = $this->repository->createQueryBuilder('j')
            ->select('j, u, c')
            ->join('j.user', 'u')
            ->join('j.city', 'c')
            ->where('j.id = :id')
            ->setParameter('id', $id)
            ->getQuery();

        try {
            $job = $q->getSingleResult();
        } catch (NoResultException $e) {
            $job = null;
        }

        return $job;
    }

    public function findAllJobs()
    {
        return $this->repository->findAll();
    }

    protected function doUpdateEducation($job)
    {
        $this->em->persist($job);
        $this->em->flush();
    }

    public function deleteJob(JobInterface $job)
    {
        $this->em->remove($job);
        $this->em->flush();
    }

    public function isNewJob(JobInterface $job)
    {
        return !$this->em->getUnitOfWork()->isInIdentityMap($job);
    }

    public function getClass()
    {
        return $this->class;
    }
}