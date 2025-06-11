<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Recipe;
use Illuminate\Http\Request;

class RecipeController extends Controller
{
    // GET /api/recipes
    public function index(Request $request)
    {
        // Si filtre par ingrédient demandé
        if ($request->has('ingredient')) {
            $ingredient = $request->input('ingredient');
            $recipes = Recipe::whereJsonContains('ingredients', $ingredient)->get();
        } else {
            $recipes = Recipe::all();
        }

        return response()->json($recipes);
    }

    // POST /api/recipes
    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'ingredients' => 'required|array',
            'ingredients.*' => 'string',
        ]);

        $recipe = Recipe::create($data);

        return response()->json($recipe, 201);
    }

    // GET /api/recipes/{id}
    public function show($id)
    {
        $recipe = Recipe::findOrFail($id);
        return response()->json($recipe);
    }

    // PUT /api/recipes/{id}
    public function update(Request $request, $id)
    {
        $recipe = Recipe::findOrFail($id);

        $data = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'ingredients' => 'sometimes|required|array',
            'ingredients.*' => 'string',
        ]);

        $recipe->update($data);

        return response()->json($recipe);
    }

    // DELETE /api/recipes/{id}
    public function destroy($id)
    {
        $recipe = Recipe::findOrFail($id);
        $recipe->delete();

        return response()->json(null, 204);
    }
}
