@extends('layouts.app')

@section('content')
<div class="h-full flex flex-col" x-data="{ 
    showRecipeModal: false, 
    selectedProduct: null, 
    ingredients: [],
    availableIngredients: {{ json_encode($inventoryItems) }},
    
    openRecipe(product) {
        this.selectedProduct = product;
        this.ingredients = product.recipe_items.map(ri => ({
            inventory_item_id: ri.inventory_item_id,
            quantity: ri.quantity
        }));
        this.showRecipeModal = true;
    },
    
    addIngredient() {
        this.ingredients.push({ inventory_item_id: '', quantity: 1 });
    },
    
    removeIngredient(index) {
        this.ingredients.splice(index, 1);
    }
}">
    <!-- Header -->
    <header class="h-16 border-b border-slate-100 flex items-center justify-between px-8 bg-white/50 backdrop-blur-sm sticky top-0 z-10">
        <div>
            <h1 class="text-xl font-bold text-slate-800">Recipe Mapping</h1>
            <p class="text-xs text-slate-400">Map ingredients to your sellable products</p>
        </div>
    </header>

    <main class="flex-1 overflow-y-auto p-8">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            @foreach($products as $product)
            <div class="bg-white rounded-[2rem] border border-slate-100 p-6 shadow-sm hover:shadow-md transition-all group">
                <div class="flex items-start justify-between mb-4">
                    <div class="w-16 h-16 bg-slate-50 rounded-2xl overflow-hidden flex-shrink-0 flex items-center justify-center text-slate-300">
                        @if($product->image)
                            <img src="{{ asset('storage/' . $product->image) }}" class="w-full h-full object-cover">
                        @else
                            <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                        @endif
                    </div>
                    <span class="px-3 py-1 bg-indigo-50 text-[10px] font-black text-indigo-600 rounded-full uppercase">{{ $product->recipe_items_count ?? count($product->recipeItems) }} Ingredients</span>
                </div>
                
                <h3 class="font-bold text-slate-800 leading-tight mb-1">{{ $product->name }}</h3>
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-4">{{ $product->category->name }}</p>
                
                <div class="space-y-1 mb-6">
                    @forelse($product->recipeItems->take(3) as $ri)
                        <div class="flex justify-between text-[11px] text-slate-500">
                            <span>{{ $ri->inventoryItem->name }}</span>
                            <span class="font-bold text-slate-700">{{ $ri->quantity }} {{ $ri->inventoryItem->unit_type }}</span>
                        </div>
                    @empty
                        <p class="text-[11px] text-amber-500 italic">No recipe mapped</p>
                    @endforelse
                    @if(count($product->recipeItems) > 3)
                        <p class="text-[10px] text-slate-400 font-bold">+ {{ count($product->recipeItems) - 3 }} more...</p>
                    @endif
                </div>

                <button @click="openRecipe({{ json_encode($product->load('recipeItems.inventoryItem')) }})" class="w-full py-3 bg-slate-50 border border-slate-100 rounded-2xl text-xs font-black text-slate-600 hover:bg-indigo-600 hover:text-white hover:border-indigo-600 transition-all active:scale-[0.98]">
                    MANAGE RECIPE
                </button>
            </div>
            @endforeach
        </div>
    </main>

    <!-- Recipe Modal -->
    <div x-show="showRecipeModal" class="fixed inset-0 z-50 flex items-center justify-end" x-cloak>
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm" @click="showRecipeModal = false"></div>
        <div class="relative w-full max-w-md h-full bg-white shadow-2xl flex flex-col p-8" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0">
            <div class="flex items-center justify-between mb-8">
                <div>
                    <h3 class="text-2xl font-black text-slate-800" x-text="selectedProduct?.name"></h3>
                    <p class="text-xs text-slate-400">Map ingredients and quantities</p>
                </div>
                <button @click="showRecipeModal = false" class="p-2 text-slate-400 hover:text-slate-600 transition-colors">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>

            <form :action="'/inventory/recipes/' + selectedProduct?.id" method="POST" class="flex-1 flex flex-col overflow-hidden">
                @csrf
                <div class="flex-1 overflow-y-auto space-y-4 pr-2">
                    <template x-for="(ing, index) in ingredients" :key="index">
                        <div class="p-4 bg-slate-50 rounded-3xl border border-slate-100 space-y-3">
                            <div class="flex items-center justify-between">
                                <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest" x-text="'Ingredient #' + (index + 1)"></span>
                                <button type="button" @click="removeIngredient(index)" class="text-red-400 hover:text-red-600">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                </button>
                            </div>
                            <select :name="'ingredients[' + index + '][inventory_item_id]'" x-model="ing.inventory_item_id" required class="w-full bg-white border-none rounded-xl px-4 py-3 text-sm text-slate-800 focus:ring-2 focus:ring-indigo-100">
                                <option value="">Select Item</option>
                                <template x-for="avail in availableIngredients" :key="avail.id">
                                    <option :value="avail.id" x-text="avail.name + ' (' + avail.unit_type + ')'" :selected="avail.id == ing.inventory_item_id"></option>
                                </template>
                            </select>
                            <div class="flex items-center gap-3">
                                <input type="number" step="0.001" :name="'ingredients[' + index + '][quantity]'" x-model="ing.quantity" required class="flex-1 bg-white border-none rounded-xl px-4 py-3 text-sm text-slate-800 focus:ring-2 focus:ring-indigo-100" placeholder="Qty">
                                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest w-12" x-text="availableIngredients.find(a => a.id == ing.inventory_item_id)?.unit_type || 'unit'"></span>
                            </div>
                        </div>
                    </template>

                    <button type="button" @click="addIngredient()" class="w-full py-4 border-2 border-dashed border-slate-200 rounded-3xl text-[10px] font-black text-slate-400 uppercase tracking-widest hover:border-indigo-200 hover:text-indigo-400 transition-all">
                        + ADD INGREDIENT
                    </button>
                </div>

                <div class="pt-8 mt-auto">
                    <button type="submit" class="w-full bg-indigo-600 text-white rounded-2xl py-4 font-black shadow-lg shadow-indigo-100 hover:bg-indigo-700 transition-all">
                        SAVE RECIPE
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
