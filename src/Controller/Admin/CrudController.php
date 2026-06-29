<?php

namespace App\Controller\Admin;

use App\Dto\SearchDtoInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\SerializerInterface;

abstract class CrudController extends AbstractController
{
    public function __construct(
        private readonly SerializerInterface $serializer
    ) {

    }

    /**
     * Construit un formulaire de recherche générique
     */
    protected function buildForm(string $formClass, array $formOptions = [], ?object $data = null): FormInterface
    {
        return $this->createForm($formClass, $data, $formOptions);
    }

    /**
     * Initialise les paramètres de recherche à partir de la requête provenant d'un datatable
     */
    protected function initSearch(Request $request, string $searchObjectClass): SearchDtoInterface
    {
        // Prepare filters
        $parsedFilters = [];
        $filters = $request->request->get('filters', '');
        parse_str($filters, $parsedFilters);

        $searchFilters = array_shift($parsedFilters);

        $search = new $searchObjectClass();
        if (!empty($searchFilters)) {
            // Clean filters
            $searchFilters = array_filter($searchFilters, function ($value) {
                return $value !== null && $value !== false && $value !== '';
            });

            $search = $this->serializer->deserialize(json_encode($searchFilters), $searchObjectClass, 'json');
        }

        // Add GET parameters if provided
        foreach ($request->query->all() as $key => $value) {
            if (property_exists($search, $key)) {
                $search->$key = $value;
            }
        }

        return $search;
    }
}