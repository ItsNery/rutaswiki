<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTransitRouteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'route_number' => 'nullable|string|max:50',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'transport_type' => 'required|string|in:bus,combi,metro,tram,trolley,other',
            'color' => 'required|string|max:7|regex:/^#[0-9A-Fa-f]{6}$/',
            'round_trip' => 'nullable|boolean',
            'has_designated_stops' => 'nullable|boolean',
            'additional_cities' => 'nullable|array',
            'additional_cities.*' => 'nullable|string',

            'geometry' => 'required|array',
            'geometry.type' => 'required|string|in:LineString',
            'geometry.coordinates' => 'required|array|min:2',
            'geometry.coordinates.*' => 'required|array|size:2',
            'geometry.coordinates.*.0' => 'required|numeric|between:-180,180',
            'geometry.coordinates.*.1' => 'required|numeric|between:-90,90',

            'geometry_return' => 'nullable|array',
            'geometry_return.type' => 'required_with:geometry_return|string|in:LineString,MultiLineString',
            'geometry_return.coordinates' => 'required_with:geometry_return|array',

            'stops' => 'nullable|array',
            'stops.*.name' => 'required|string|max:255',
            'stops.*.latitude' => 'required|numeric|between:-90,90',
            'stops.*.longitude' => 'required|numeric|between:-180,180',
            'stops.*.description' => 'nullable|string',

            'schedules' => 'required|array|min:1',
            'schedules.*.day_type' => 'required|string|in:weekday,saturday,sunday,holiday',
            'schedules.*.start_time' => 'required|string|regex:/^\d{2}:\d{2}$/',
            'schedules.*.end_time' => 'required|string|regex:/^\d{2}:\d{2}$/',
            'schedules.*.frequency_minutes' => 'required|integer|min:1',
        ];
    }

    public function messages(): array
    {
        return [
            'geometry.required' => 'Debes dibujar una ruta en el mapa.',
            'geometry.type.in' => 'La geometría debe ser de tipo LineString.',
            'geometry.coordinates.min' => 'La ruta debe tener al menos 2 puntos.',
            'geometry.coordinates.*.size' => 'Cada coordenada debe tener exactamente 2 valores (longitud y latitud).',
            'geometry.coordinates.*.0.between' => 'La longitud debe estar entre -180 y 180.',
            'geometry.coordinates.*.1.between' => 'La latitud debe estar entre -90 y 90.',
            'color.regex' => 'El color debe ser un hexadecimal válido (ej. #ff0000).',
        ];
    }

    protected function prepareForValidation()
    {
        if ($this->has('geometry') && is_string($this->input('geometry'))) {
            $this->merge([
                'geometry' => json_decode($this->input('geometry'), true),
            ]);
        }
        if ($this->has('geometry_return') && is_string($this->input('geometry_return'))) {
            $this->merge([
                'geometry_return' => json_decode($this->input('geometry_return'), true),
            ]);
        }
    }
}
