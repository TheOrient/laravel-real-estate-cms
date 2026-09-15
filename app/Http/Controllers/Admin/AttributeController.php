<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Attribute\AssignToCategoryRequest;
use App\Http\Requests\Admin\Attribute\StoreAttributeRequest;
use App\Http\Requests\Admin\Attribute\UpdateAttributeRequest;
use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\Category;
use App\Models\Language;
use App\Services\AttributeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class AttributeController extends Controller
{
    protected $attributeService;

    public function __construct(AttributeService $attributeService)
    {
        $this->attributeService = $attributeService;
    }

    /**
     * Display a listing of the attributes.
     */
    public function index()
    {
        $attributes = $this->attributeService->getAllAttributes();
        return view('admin.attributes.index', compact('attributes'));
    }

    /**
     * Show the form for creating a new attribute.
     */
    public function create()
    {
        $languages = $this->attributeService->getActiveLanguages();
        $descriptions = $this->attributeService->prepareEmptyAttributeDescriptions();
        $valueDescriptions = $this->attributeService->prepareEmptyAttributeValueDescriptions(1);

        return view('admin.attributes.create', compact('languages', 'descriptions', 'valueDescriptions'));
    }

    /**
     * Store a newly created attribute in storage.
     */
    public function store(StoreAttributeRequest $request)
    {
        try {
            $data = $request->validateAndPrepare();
            $this->attributeService->createAttribute(
                $data['attributeData'],
                $data['options'],
                $data['descriptions'],
                $data['valueDescriptions']
            );

            return redirect()->route('admin.attributes.index')
                ->with('success', __('admin/attributes.created'));
        } catch (\Exception $e) {
            return back()->withInput()->with('error', __('Error creating attribute: ') . $e->getMessage());
        }
    }

    /**
     * Show the form for editing the specified attribute.
     */
    public function edit(Attribute $attribute)
    {
        $attribute->load('values.descriptions');
        $languages = $this->attributeService->getActiveLanguages();
        $descriptions = $this->attributeService->prepareAttributeDescriptions($attribute);
        $valueDescriptions = $this->attributeService->prepareAttributeValueDescriptions($attribute);

        return view('admin.attributes.edit', compact('attribute', 'languages', 'descriptions', 'valueDescriptions'));
    }

    /**
     * Update the specified attribute in storage.
     */
    public function update(UpdateAttributeRequest $request, Attribute $attribute)
    {
        try {
            $data = $request->validateAndPrepare();
            $this->attributeService->updateAttribute(
                $attribute,
                $data['attributeData'],
                $data['options'],
                $data['descriptions'],
                $data['valueDescriptions']
            );

            return redirect()->route('admin.attributes.index')
                ->with('success', __('admin/attributes.updated'));
        } catch (\Exception $e) {
            return back()->withInput()->with('error', __('Error updating attribute: ') . $e->getMessage());
        }
    }

    /**
     * Remove the specified attribute from storage.
     */
    public function destroy(Attribute $attribute)
    {
        try {
            $this->attributeService->deleteAttribute($attribute);
            return redirect()->route('admin.attributes.index')
                ->with('success', __('admin/attributes.deleted'));
        } catch (\Exception $e) {
            return back()->with('error', __('Error deleting attribute: ') . $e->getMessage());
        }
    }

    /**
     * Show categories to assign attributes
     */
    public function showCategoryAttributes()
    {
        $data = $this->attributeService->getCategoryAttributeData();
        return view('admin.attributes.category_attributes', $data);
    }

    /**
     * Assign attributes to a category
     */
    public function assignToCategory(AssignToCategoryRequest $request, Category $category)
    {
        $attributeData = $request->getAttributeData();
        $this->attributeService->assignAttributesToCategory($category, $attributeData);

        return response()->json(['success' => true, 'message' => 'Attributes assigned successfully']);
    }

    /**
     * Get attributes for a category (for Ajax requests)
     */
    public function getCategoryAttributes(Category $category)
    {
        $attributes = $this->attributeService->getCategoryAttributes($category);
        return response()->json($attributes);
    }

    /**
     * Get filterable attributes for a category (for Ajax requests)
     */
    public function getFilterableAttributes(Category $category)
    {
        $attributes = $this->attributeService->getFilterableAttributes($category);
        return response()->json($attributes);
    }
}
