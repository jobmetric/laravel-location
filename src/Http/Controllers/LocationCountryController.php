<?php

namespace JobMetric\Location\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use JobMetric\Language\Facades\Language;
use JobMetric\Location\Facades\Country;
use JobMetric\Location\Http\Requests\StoreCountryRequest;
use JobMetric\Location\Http\Requests\UpdateCountryRequest;
use JobMetric\Location\Http\Resources\LocationCountryResource;
use JobMetric\Location\Models\LocationCountry as LocationCountryModel;
use JobMetric\Panelio\Facades\Breadcrumb;
use JobMetric\Panelio\Facades\Button;
use JobMetric\Panelio\Facades\Datatable;
use JobMetric\Panelio\Http\Controllers\Controller;
use Throwable;

class LocationCountryController extends Controller
{
    private array $route;

    public function __construct()
    {
        if (request()->route()) {
            $parameters = request()->route()->parameters();

            $this->route = [
                'index' => route('location.location_country.index', $parameters),
                'create' => route('location.location_country.create', $parameters),
                'store' => route('location.location_country.store', $parameters),
                'options' => route('location.location_country.options', $parameters),
            ];
        }
    }

    /**
     * Display a listing of the location country.
     *
     * @param string $panel
     * @param string $section
     *
     * @return View|JsonResponse
     * @throws Throwable
     */
    public function index(string $panel, string $section): View|JsonResponse
    {
        if (request()->ajax()) {
            $query = Country::query();

            return Datatable::of($query, resource_class: LocationCountryResource::class);
        }

        // Set data location country
        $data['name'] = trans('location::base.location_country.name');

        DomiTitle($data['name']);

        // Add breadcrumb
        add_breadcrumb_base($panel, $section);
        Breadcrumb::add($data['name']);

        // add button
        Button::add($this->route['create']);
        Button::delete();
        Button::status();

        DomiLocalize('country', [
            'route' => $this->route['index'],
        ]);

        DomiScript('assets/vendor/location/js/country/list.js');

        $data['route'] = $this->route['options'];

        return view('location::country.list', $data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param string $panel
     * @param string $section
     *
     * @return View
     */
    public function create(string $panel, string $section): View
    {
        $data['mode'] = 'create';

        // Set data location country
        $data['name'] = trans('location::base.location_country.name');

        DomiTitle(trans('location::base.form.location_country.create.title'));

        // Add breadcrumb
        add_breadcrumb_base($panel, $section);
        Breadcrumb::add($data['name'], $this->route['index']);
        Breadcrumb::add(trans('location::base.form.location_country.create.title'));

        // add button
        Button::save();
        Button::saveNew();
        Button::saveClose();
        Button::cancel($this->route['index']);

        DomiScript('assets/vendor/location/js/country/form.js');

        $data['action'] = $this->route['store'];
        $data['flags'] = Language::getFlags();

        return view('location::country.form', $data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreCountryRequest $request
     * @param string $panel
     * @param string $section
     *
     * @return RedirectResponse
     * @throws Throwable
     */
    public function store(StoreCountryRequest $request, string $panel, string $section): RedirectResponse
    {
        $form_data = $request->all();

        $location_country = Country::store($request->validated());

        if ($location_country['ok']) {
            $this->alert($location_country['message']);

            if ($form_data['save'] == 'save.new') {
                return back();
            }

            if ($form_data['save'] == 'save.close') {
                return redirect()->to($this->route['index']);
            }

            // btn save
            return redirect()->route('location.location_country.edit', [
                'panel' => $panel,
                'section' => $section,
                'location_country' => $location_country['data']->id
            ]);
        }

        $this->alert($location_country['message'], 'danger');

        return back();
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param string $panel
     * @param string $section
     * @param LocationCountryModel $location_country
     *
     * @return View
     */
    public function edit(string $panel, string $section, LocationCountryModel $location_country): View
    {
        $data['mode'] = 'edit';

        // Set data location country
        $data['name'] = trans('location::base.location_country.name');

        DomiTitle(trans('location::base.form.location_country.edit.title'));

        // Add breadcrumb
        add_breadcrumb_base($panel, $section);
        Breadcrumb::add($data['name'], $this->route['index']);
        Breadcrumb::add(trans('location::base.form.location_country.edit.title'));

        // add button
        Button::save();
        Button::saveNew();
        Button::saveClose();
        Button::cancel($this->route['index']);

        DomiScript('assets/vendor/location/js/country/form.js');

        $data['action'] = route('location.location_country.update', [
            'panel' => $panel,
            'section' => $section,
            'location_country' => $location_country->id
        ]);

        $data['flags'] = Language::getFlags();
        $data['country'] = $location_country;

        return view('location::country.form', $data);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateCountryRequest $request
     * @param string $panel
     * @param string $section
     * @param LocationCountryModel $location_country
     *
     * @return RedirectResponse
     * @throws Throwable
     */
    public function update(UpdateCountryRequest $request, string $panel, string $section, LocationCountryModel $location_country): RedirectResponse
    {
        $form_data = $request->all();

        $location_country = Country::update($location_country->id, $request->validated());

        if ($location_country['ok']) {
            $this->alert($location_country['message']);

            if ($form_data['save'] == 'save.new') {
                return redirect()->to($this->route['create']);
            }

            if ($form_data['save'] == 'save.close') {
                return redirect()->to($this->route['index']);
            }

            // btn save
            return redirect()->route('location.location_country.edit', [
                'panel' => $panel,
                'section' => $section,
                'location_country' => $location_country['data']->id
            ]);
        }

        $this->alert($location_country['message'], 'danger');

        return back();
    }

    /**
     * Delete the specified resource from storage.
     *
     * @param array $ids
     * @param mixed $params
     * @param string|null $alert
     * @param string|null $danger
     *
     * @return bool
     * @throws Throwable
     */
    public function deletes(array $ids, mixed $params, string &$alert = null, string &$danger = null): bool
    {
        try {
            foreach ($ids as $id) {
                Country::delete($id);
            }

            $alert = trans_choice('location::base.messages.deleted_items', count($ids), [
                'name' => trans('location::base.model_name.country'),
            ]);

            return true;
        } catch (Throwable $e) {
            $danger = $e->getMessage();

            return false;
        }
    }

    /**
     * Change Status the specified resource from storage.
     *
     * @param array $ids
     * @param bool $value
     * @param mixed $params
     * @param string|null $alert
     * @param string|null $danger
     *
     * @return bool
     * @throws Throwable
     */
    public function changeStatus(array $ids, bool $value, mixed $params, string &$alert = null, string &$danger = null): bool
    {
        try {
            foreach ($ids as $id) {
                Country::update($id, ['status' => $value]);
            }

            if ($value) {
                $alert = trans_choice('location::base.messages.status.enable', count($ids), [
                    'name' => trans('location::base.model_name.country'),
                ]);
            } else {
                $alert = trans_choice('location::base.messages.status.disable', count($ids), [
                    'name' => trans('location::base.model_name.country'),
                ]);
            }

            return true;
        } catch (Throwable $e) {
            $danger = $e->getMessage();

            return false;
        }
    }
}
