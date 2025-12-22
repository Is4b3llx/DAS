@php
    $solicitud = $solicitud ?? new \App\Models\Solicitud;

    $pers = optional($solicitud->solicitante ?? null);
    $dest = optional($solicitud->destino ?? null);

    $latValue = old('latitud', $dest->latitud ?? null);
    $lngValue = old('longitud', $dest->longitud ?? null);
    $defaultLat = $latValue ?: -17.8146;
    $defaultLng = $lngValue ?: -63.1561;
    $defaultZoom = $latValue ? 13 : 6;
    $tipoEmergencia = $tipoEmergencia ?? collect();
@endphp



<div class="row">
    <div class="col-md-6">
        <div class="form-group mb-3">
            <label for="nombre">Nombre</label>
            <input required type="text" name="nombre" id="nombre"
                   class="form-control @error('nombre') is-invalid @enderror"
                   value="{{ old('nombre', $pers->nombre) }}"
                   placeholder="Ingrese el nombre del solicitante">
            @error('nombre') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
    </div>

    <div class="col-md-6">
        <div class="form-group mb-3">
            <label for="apellido">Apellido</label>
            <input required type="text" name="apellido" id="apellido"
                   class="form-control @error('apellido') is-invalid @enderror"
                   value="{{ old('apellido', $pers->apellido) }}"
                   placeholder="Ingrese el apellido del solicitante">
            @error('apellido') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
    </div>

    <div class="col-md-6">
        <div class="form-group mb-3">
            <label for="carnet_identidad">Carnet de Identidad</label>
            <input required type="text" name="carnet_identidad" id="carnet_identidad"
                   class="form-control @error('carnet_identidad') is-invalid @enderror"
                   value="{{ old('carnet_identidad', $pers->ci) }}"
                   placeholder="Ingrese el número de CI">
            @error('carnet_identidad') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
    </div>

    <div class="col-md-6">
        <div class="form-group mb-3">
            <label for="correo_electronico">Correo Electrónico</label>
            <input required type="email" name="correo_electronico" id="correo_electronico"
                   class="form-control @error('correo_electronico') is-invalid @enderror"
                   value="{{ old('correo_electronico', $pers->email) }}"
                   placeholder="correo@ejemplo.com">
            @error('correo_electronico') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
    </div>

    <div class="col-md-6">
        <div class="form-group mb-3">
            <label for="nro_celular">Número de Celular</label>
            <input required type="text" name="nro_celular" id="nro_celular"
                   class="form-control @error('nro_celular') is-invalid @enderror"
                   value="{{ old('nro_celular', $pers->telefono) }}"
                   placeholder="Ej. 70000000"
                   pattern="[0-9]+"
                   title="Solo se permiten números"
                   oninput="this.value = this.value.replace(/[^0-9]/g, '')">
            @error('nro_celular') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
    </div>
    <div class="col-md-6 text-center rounded-3 mt-2 mb-2 pt-2" style="background-color:#ffe89c; border-radius:8px; font-size:14px;">
        <p style="color:black;">Los datos que ingreso es esta seccion deben corresponder a la persona que recibira el paquete en el lugar.  En caso de no poder asistir, llena la seccion de contacto de referencia.</p>
    </div>
    <div class="rounded-3 col-md-12 gap-1" style="background-color: aliceblue; border-radius: 7px;">
        <p style="font-size: large; font-weight: 600;">Contacto de Referencia (Opcional)</p>
        <div class="d-flex">
            <div class="col-md-6">
                <div class="form-group ">
                    <label for="nombre_referencia">Nombre Completo</label>
                    <input type="text" name="nombre_referencia" id="nombre_referencia"
                        class="form-control @error('nombre_referencia') is-invalid @enderror"
                        value="{{ old('nombre_referencia', $solicitud->nombre_referencia) }}"
                        placeholder="Nombre y Apellidos"
                        pattern="[A-Za-z\s]+"
                        title="Solo se permiten letras y espacios"
                        oninput="this.value = this.value.replace(/[^A-Za-z\s]/g, '')">
                    @error('nombre_referencia') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    <small>Asegurate de que esta persona pueda recibir el paquete en el destino.</small>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label for="celular_referencia">Número de Celular</label>
                    <input type="text" name="celular_referencia" id="celular_referencia"
                        class="form-control @error('celular_referencia') is-invalid @enderror"
                        value="{{ old('celular_referencia', $solicitud->celular_referencia) }}"
                        placeholder="Ej. 70000000"
                        pattern="[0-9]+"
                        title="Solo se permiten números"
                        oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                    @error('nombre_referencia') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>

        </div>
            
    </div>
    
    <div class="col-md-12 mt-2 mb-2 pt-2 pb-2" style="background-color:azure; border-radius: 7px;">
        <p style="font-size: large; font-weight: 600;">Ubicación para la Entrega</p>
        <div class="form-group mb-3 col-md-6">
            <label for="comunidad_solicitante">Comunidad Solicitante</label>
            <input required type="text" name="comunidad_solicitante" id="comunidad_solicitante"
                   class="form-control @error('comunidad_solicitante') is-invalid @enderror"
                   value="{{ old('comunidad_solicitante', $dest->comunidad) }}"
                   placeholder="Nombre de la comunidad o institución">
            @error('comunidad_solicitante') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
    
        <div class="">
            <div class="form-group mb-3">
                <label for="mapa-ubicacion">Seleccione la Ubicación en el Mapa - <small> Aqui se entregaran los insumos</small></label>
                <div class="position-relative">
                    <div class="input-group mb-2 col-md-6 p-0">
                        <input type="text"
                            id="search-ubicacion"
                            class="form-control bg-light"
                            placeholder="Buscar comunidad, barrio o dirección (ej. Univalle, 4to anillo)...">
                        <div class="input-group-append">
                            <button type="button"
                                    class="btn btn-info"
                                    id="btnBuscarUbicacion">
                                <i class="fas fa-search"></i> Buscar
                            </button>
                        </div>
                    </div>
                    <div id="search-suggestions"
                        class="list-group position-absolute"
                        style="z-index: 1050; max-height: 250px; overflow-y: auto; width: 100%; display:none;">
                    </div>
                </div>

                <div id="mapa-ubicacion"
                    data-lat="{{ $defaultLat }}"
                    data-lng="{{ $defaultLng }}"
                    data-zoom="{{ $defaultZoom }}"
                    style="height: 400px; width: 100%; border: 1px solid #ddd; border-radius: 4px;">
                </div>

                <small class="form-text text-muted">
                    Puede buscar una dirección o hacer clic en el mapa para seleccionar la ubicación.
                    La dirección se rellenará automáticamente.
                </small>

                @error('ubicacion') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                @error('latitud') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                @error('longitud') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
            </div>
        </div>
        <div class="col-md-2" hidden>
            <div class="form-group mb-3">
                <label for="latitud">Latitud</label>
                <input required type="number" step="any" name="latitud" id="latitud" readonly
                    class="form-control @error('latitud') is-invalid @enderror"
                    value="{{ old('latitud', $dest->latitud) }}" placeholder="-17.78">
                @error('latitud') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
        </div>
        
        <div class="col-md-2" hidden>
            <div class="form-group mb-3">
                <label for="longitud">Longitud</label>
                <input required type="number" step="any" name="longitud" id="longitud" readonly
                    class="form-control @error('longitud') is-invalid @enderror"
                    value="{{ old('longitud', $dest->longitud) }}" placeholder="-63.18">
                @error('longitud') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
        </div>
        <div class="col-md-12 d-flex">
            <div class="col-md-6">
                <div class="form-group mb-3">
                    <label for="provincia">Provincia</label>
                    <input required type="text" name="provincia" id="provincia"
                        class="form-control @error('provincia') is-invalid @enderror"
                        value="{{ old('provincia', $dest->provincia) }}"
                        placeholder="Ej. Limoncito" readonly>
                    @error('provincia') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group mb-3">
                    <label for="ubicacion">Ubicación (dirección/zona)</label>
                    <input required type="text" name="ubicacion" id="ubicacion" readonly
                        class="form-control @error('ubicacion') is-invalid @enderror"
                        value="{{ old('ubicacion', $dest->direccion) }}"
                        placeholder="Se seleccionará automáticamente del mapa">
                    @error('ubicacion') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>
        </div>
        
    </div>

    <div class="col-md-3">
        <div class="form-group mb-3">
            <label for="cantidad_personas">Cantidad de Personas Afectadas</label>
            <input required type="number" min="1" name="cantidad_personas" id="cantidad_personas"
                   class="form-control @error('cantidad_personas') is-invalid @enderror"
                   value="{{ old('cantidad_personas', $solicitud->cantidad_personas) }}">
            @error('cantidad_personas') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
    </div>

    @php
        $fechaInicioValue = old('fecha_inicio');
        if (!$fechaInicioValue) {
            if ($solicitud->fecha_inicio) {
                $fechaInicioValue = $solicitud->fecha_inicio->format('Y-m-d');
            } else {
                $fechaInicioValue = now()->format('Y-m-d');
            }
        }
        $fechaNecesidadValue = old('fecha_necesidad');
        if (!$fechaNecesidadValue) {
            if ($solicitud->fecha_necesidad) {
                $fechaNecesidadValue = $solicitud->fecha_necesidad->format('Y-m-d');
            } else {
                $fechaNecesidadValue = now()->addDays(3)->format('Y-m-d');
            }
        }
    @endphp

    <div class="col-md-3">
        <div class="form-group mb-3">
            <label for="fecha_inicio">Fecha de Inicio</label>
            <input required type="date" name="fecha_inicio" id="fecha_inicio"
                   class="form-control @error('fecha_inicio') is-invalid @enderror"
                   value="{{ $fechaInicioValue }}">
            @error('fecha_inicio') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
    </div>
    <div class="col-md-3">
        <div class="form-group">
            <label for="fecha_necesidad">Fecha de Necesidad</label>
            <input required type="date" name="fecha_necesidad" id="fecha_necesidad"
                class="form-control @error('fecha_necesidad') is-invalid @enderror"
                 value="{{ $fechaNecesidadValue }}">
            <small>Indica la fecha limite para recibir los insumos.</small>
            @error('fecha_necesidad') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
    </div>

    <div class="col-md-3">
        <div class="form-group mb-3">
            <label for="id_tipoemergencia">Tipo de Emergencia</label><br>
            <select required name="id_tipoemergencia" id="id_tipoemergencia"
                    class="form-control @error('id_tipoemergencia') is-invalid @enderror">
                <option value="">Seleccione un tipo</option>
                @foreach($tipoEmergencia as $tipo)
                    <option value="{{ $tipo->id_emergencia }}"
                        {{ old('id_tipoemergencia', $solicitud->id_tipoemergencia) == $tipo->id_emergencia ? 'selected' : '' }}>
                        {{ $tipo->emergencia }}
                    </option>
                @endforeach
            </select>
            @error('id_tipoemergencia') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
        <input type="hidden" name="tipo_emergencia"
            value="{{ old('tipo_emergencia', optional($solicitud->tipoEmergencia)->emergencia) }}">
    </div>


    <div class="col-md-6">
        <div class="form-group mb-3">
            <label for="insumos_necesarios">Insumos Necesarios</label><br>
            <div class="d-flex">
                <button type="button" class="btn btn-info mb-3 mr-3" style="min-width: fit-content;" data-toggle="modal" data-target="#insumosModal">
                    Seleccionar Insumos
                </button>
                <input type="text-area" readonly class="form-control" name="insumos_necesarios" id="insumos_necesarios" value="{{ old('insumos_necesarios', $solicitud->insumos_necesarios) }}">
                @error('insumos_necesarios') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
        </div>

        <!-- Modal -->
        <div class="modal fade" id="insumosModal" tabindex="-1" aria-labelledby="insumosModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title" id="insumosModalLabel">Seleccionar Productos</h5>
                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-8">
                                <h6>Seleccione los productos:</h6>
                                <div id="productos-list" class="list-group">
                                </div>
                                <nav aria-label="Page navigation" class="mt-3">
                                    <ul id="productos-pagination" class="pagination justify-content-center"></ul>
                                </nav>
                            </div>
                            <div class="col-md-4">
                                <h6>Productos seleccionados:</h6>
                                <ul id="productos-seleccionados" class="list-group">
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                        <button type="button" class="btn btn-success" id="guardarInsumos">Guardar</button>
                    </div>
                </div>
            </div>
        </div>

    @php
        $codigoSeguimiento = old('codigo_seguimiento', $solicitud->codigo_seguimiento);
        if (empty($codigoSeguimiento)) {
            $codigoSeguimiento =
                \Illuminate\Support\Str::upper(\Illuminate\Support\Str::random(3))
                . '-' .
                rand(1000, 9999);
        }
    @endphp

</div>
    <input type="hidden"
          name="codigo_seguimiento"
          id="codigo_seguimiento"
          value="{{ $codigoSeguimiento }}">

<div class="col-md-12 justify-content-between d-flex text-right mt-3 ml-2">
    <div class="mr-3">
        @auth
            <a href="{{ route('solicitud.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Volver
            </a>
        @endauth
    </div>
   <div class="align-items-right">
    <button type="submit" class="btn btn-info" id="btn-submit-solicitud">
        <i class="fas fa-save"></i> Enviar Solicitud
    </button>
   </div>
</div>

<script>
(function() {
  function initMap() {
    if (typeof L === 'undefined') {
      console.warn('Leaflet no está cargado. Reintentando...');
      setTimeout(initMap, 100);
      return;
    }

    const mapContainer = document.getElementById('mapa-ubicacion');
    if (!mapContainer) return;

    let defaultLat  = parseFloat(mapContainer.dataset.lat || "-17.81463325");
    let defaultLng  = parseFloat(mapContainer.dataset.lng || "-63.15615466");
    let defaultZoom = parseInt(mapContainer.dataset.zoom || "6", 10);

    const latInput       = document.getElementById('latitud');
    const lngInput       = document.getElementById('longitud');
    const ubicacionInput = document.getElementById('ubicacion');
    const provinciaInput = document.getElementById('provincia');

    const searchInput        = document.getElementById('search-ubicacion');
    const searchBtn          = document.getElementById('btnBuscarUbicacion');
    const suggestionsContainer = document.getElementById('search-suggestions');

        const map = L.map('mapa-ubicacion').setView([defaultLat, defaultLng], defaultZoom);
        const hotspotBaseUrl = @json(rtrim(config('services.gateway.base_url'), '/'));
        const HOTSPOT_API_URL = `${hotspotBaseUrl}/api/gateway/brigadas/hotspots`;
        const hotspotLayer = L.layerGroup().addTo(map);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
      attribution: '&copy; OpenStreetMap',
      maxZoom: 19
    }).addTo(map);

    let marker = null;

    function setMarker(lat, lng) {
      latInput.value = lat;
      lngInput.value = lng;

      if (marker) {
        marker.setLatLng([lat, lng]);
      } else {
        marker = L.marker([lat, lng]).addTo(map);
      }
      map.setView([lat, lng], 17);
    }

    function fillFromSearchResult(place) {
        const lat = parseFloat(place.lat);
        const lng = parseFloat(place.lon);

        if (isNaN(lat) || isNaN(lng)) {
            console.warn('Coordenadas inválidas en resultado de búsqueda:', place);
            return;
        }

        setMarker(lat, lng);
        reverseGeocodeThrottled(lat, lng);
        if (mapContainer && mapContainer.scrollIntoView) {
            mapContainer.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    }


    function clearSuggestions() {
      if (!suggestionsContainer) return;
      suggestionsContainer.innerHTML = '';
      suggestionsContainer.style.display = 'none';
    }

    function renderSuggestions(results) {
      if (!suggestionsContainer) return;

      suggestionsContainer.innerHTML = '';

      if (!results || !results.length) {
        const emptyItem = document.createElement('div');
        emptyItem.className = 'list-group-item text-muted';
        emptyItem.textContent = 'No se encontraron lugares en Bolivia.';
        suggestionsContainer.appendChild(emptyItem);
        suggestionsContainer.style.display = 'block';
        return;
      }

      results.forEach(place => {
        const item = document.createElement('button');
        item.type = 'button';
        item.className = 'list-group-item list-group-item-action';

        const a = place.address || {};
        const mainName =
          a.university ||
          a.school ||
          a.hospital ||
          a.public_building ||
          a.amenity ||
          a.road ||
          a.neighbourhood ||
          a.suburb ||
          a.village ||
          a.town ||
          a.city ||
          place.display_name;

        const locParts = [];
        if (a.suburb)        locParts.push(a.suburb);
        if (a.neighbourhood) locParts.push(a.neighbourhood);
        if (a.city || a.town) locParts.push(a.city || a.town);
        if (a.state)         locParts.push(a.state);

        item.innerHTML = `
          <strong>${mainName}</strong><br>
          <small>${locParts.join(' · ')}</small>
        `;

        item.addEventListener('click', function(e) {
          e.preventDefault();
          fillFromSearchResult(place);
          clearSuggestions();
        });

        suggestionsContainer.appendChild(item);
      });

      suggestionsContainer.style.display = 'block';
    }

    function buscarUbicacion() {
      if (!searchInput) return;

      const query = searchInput.value.trim();
      if (!query || query.length < 3) {
        clearSuggestions();
        return;
      }

      const url = `https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(query)}&addressdetails=1&limit=8&countrycodes=bo`;

      if (searchBtn) {
        searchBtn.disabled = true;
        searchBtn.innerText = 'Buscando...';
      }

      fetch(url)
        .then(r => r.json())
        .then(results => {
          renderSuggestions(results);
        })
        .catch(err => {
          console.error('Error al buscar ubicación:', err);
          clearSuggestions();
        })
        .finally(() => {
          if (searchBtn) {
            searchBtn.disabled = false;
            searchBtn.innerHTML = '<i class="fas fa-search"></i> Buscar';
          }
        });
    }

    let searchTimeout = null;
    function debouncedBuscarUbicacion() {
      clearTimeout(searchTimeout);
      searchTimeout = setTimeout(buscarUbicacion, 400);
    }

    function reverseGeocode(lat, lng) {
        ubicacionInput.value = 'Cargando...';
        provinciaInput.value = 'Cargando...';

        fetch(`/geo/reverse?lat=${encodeURIComponent(lat)}&lng=${encodeURIComponent(lng)}`)
            .then(r => r.json())
            .then(data => {
            const a = data?.address || {};

            let dir = a.road ?? '';
            if (a.house_number)  dir += (dir ? ' ' : '') + a.house_number;
            if (a.neighbourhood) dir += (dir ? ', ' : '') + a.neighbourhood;
            if (a.suburb)        dir += (dir ? ', ' : '') + a.suburb;
            if (a.city || a.town) dir += (dir ? ', ' : '') + (a.city || a.town);

            ubicacionInput.value = dir || data.display_name || `${lat}, ${lng}`;
            provinciaInput.value =
                a.village ?? a.town ?? a.city ?? a.municipality ?? a.state ?? a.region ?? a.county ?? '';
            })
            .catch(() => {
            ubicacionInput.value = `${lat}, ${lng}`;
            provinciaInput.value = '';
            }
        );
    }
    let reverseTimeout = null;
    function reverseGeocodeThrottled(lat, lng) {
    clearTimeout(reverseTimeout);
    reverseTimeout = setTimeout(() => reverseGeocode(lat, lng), 450);
    }

    map.on('click', function(e) {
      clearSuggestions();
      setMarker(e.latlng.lat, e.latlng.lng);
      reverseGeocodeThrottled(e.latlng.lat, e.latlng.lng);
    });

        function renderHotspots(points) {
            hotspotLayer.clearLayers();
            if (!Array.isArray(points) || !points.length) {
                return;
            }
            points.forEach(point => {
                const lat = parseFloat(point.latitude);
                const lng = parseFloat(point.longitude);
                if (isNaN(lat) || isNaN(lng)) {
                    return;
                }
                const marker = L.circleMarker([lat, lng], {
                    radius: 6,
                    color: '#d9534f',
                    weight: 2,
                    fillColor: '#f0ad4e',
                    fillOpacity: 0.8
                });
                const brillo = point.brightness ?? 'N/D';
                const confianza = (point.confidence || '').toString().toUpperCase();
                const fecha = point.date || 'Sin fecha';
                const hora = point.time || '';
                const fuente = point.source || 'Foco detectado';
                marker.bindPopup(
                    `<strong>${fuente}</strong><br>` +
                    `Brillo: ${brillo}<br>` +
                    `Confianza: ${confianza || 'N/D'}<br>` +
                    `Fecha: ${fecha} ${hora}`
                );
                marker.addTo(hotspotLayer);
            });
        }

        function loadHotspots() {
            fetch(HOTSPOT_API_URL)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Hotspot HTTP ' + response.status);
                    }
                    return response.json();
                })
                .then(payload => {
                    renderHotspots(payload?.data);
                })
                .catch(error => {
                    console.error('No se pudieron cargar los focos activos:', error);
                });
        }

    if (searchBtn && searchInput) {
      searchBtn.addEventListener('click', function(e) {
        e.preventDefault();
        buscarUbicacion();
      });

      searchInput.addEventListener('keydown', function(e) {
        if (e.key === 'Enter') {
          e.preventDefault();
          buscarUbicacion();
        }
      });

      searchInput.addEventListener('input', function() {
        debouncedBuscarUbicacion();
      });

      searchInput.addEventListener('blur', function() {
        setTimeout(clearSuggestions, 200);
      });
    }

    if (latInput.value && lngInput.value) {
      const lat = parseFloat(latInput.value);
      const lng = parseFloat(lngInput.value);
      if (!isNaN(lat) && !isNaN(lng)) {
        setMarker(lat, lng);
        reverseGeocodeThrottled(lat, lng);
        return;
      }
    }

    if (navigator.geolocation) {
      navigator.geolocation.getCurrentPosition(
        function(pos) {
          const lat = pos.coords.latitude;
          const lng = pos.coords.longitude;
          setMarker(lat, lng);
          reverseGeocodeThrottled(lat, lng);
        },
        function(err) {
            console.warn("Geolocalización rechazada o falló:", err);
            const fallbackLat = -17.722213878615346;
            const fallbackLng = -63.17462682731379;
            setMarker(fallbackLat, fallbackLng);
            reverseGeocode(fallbackLat, fallbackLng);
        },
        { enableHighAccuracy: true, timeout: 8000 }
      );
    }

        loadHotspots();
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initMap);
  } else {
    initMap();
  }
})();
</script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const submitBtn = document.getElementById('btn-submit-solicitud');
    if (!submitBtn) return;

    const form = submitBtn.closest('form');
    if (!form) return;

    form.addEventListener('submit', function () {
        if (submitBtn.disabled) {
            return;
        }

        submitBtn.disabled = true;
        submitBtn.dataset.originalText = submitBtn.innerHTML;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Enviando...';

    });
});
</script>


@section('js')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const gatewayBaseUrl = @json(rtrim(config('services.gateway.base_url'), '/'));
        const productosList = document.getElementById('productos-list');
        const productosSeleccionados = document.getElementById('productos-seleccionados');
        const insumosInput = document.getElementById('insumos_necesarios');
        const paginationContainer = document.getElementById('productos-pagination');

        let productos = [];

        const pageSize = 5;
        let currentPage = 1;

        function loadProductos() {
            productosList.innerHTML = '<div class="text-center py-4"><i class="fas fa-spinner fa-spin mr-2"></i>Cargando inventario...</div>';
            const inventarioUrl = `${gatewayBaseUrl}/api/gateway/donaciones/inventario/por-producto`;
            const simulatedProductos = [
                { id_producto: 1, nombre: 'Arroz (kg)', stock_total: 100 },
                { id_producto: 2, nombre: 'Azúcar (kg)', stock_total: 80 },
                { id_producto: 3, nombre: 'Aceite (lt)', stock_total: 60 },
                { id_producto: 4, nombre: 'Fideo (kg)', stock_total: 90 },
                { id_producto: 5, nombre: 'Sal (kg)', stock_total: 70 },
                { id_producto: 6, nombre: 'Leche en polvo (bolsa)', stock_total: 50 },
                { id_producto: 7, nombre: 'Lenteja (kg)', stock_total: 40 },
                { id_producto: 8, nombre: 'Harina (kg)', stock_total: 60 },
                { id_producto: 9, nombre: 'Jabón (barra)', stock_total: 100 },
                { id_producto: 10, nombre: 'Papel higiénico (rollo)', stock_total: 120 }
            ];
            let timeoutId;
            let didRespond = false;
            function useSimulated() {
                if (didRespond) return;
                didRespond = true;
                productos = simulatedProductos;
                currentPage = 1;
                renderPagination();
                renderPage();
                productosList.insertAdjacentHTML('afterbegin', '<div class="alert alert-warning">Lista Productos.</div>');
            }
            timeoutId = setTimeout(useSimulated, 7000); 
            fetch(inventarioUrl)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Error al obtener los productos');
                    }
                    return response.json();
                })
                .then(data => {
                    if (didRespond) return; 
                    didRespond = true;
                    clearTimeout(timeoutId);
                    productos = Array.isArray(data) ? data : [];
                    currentPage = 1;
                    renderPagination();
                    renderPage();
                })
                .catch(error => {
                clearTimeout(timeoutId);
                useSimulated();
                });
        }

        function renderPagination() {
            if (!paginationContainer) return;

            const totalPages = Math.ceil(productos.length / pageSize);
            paginationContainer.innerHTML = '';

            if (totalPages <= 1) return;

            for (let page = 1; page <= totalPages; page++) {
                const li = document.createElement('li');
                li.className = 'page-item' + (page === currentPage ? ' active' : '');

                const a = document.createElement('a');
                a.href = '#';
                a.className = 'page-link';
                a.textContent = page;

                a.addEventListener('click', function(e) {
                    e.preventDefault();
                    if (page === currentPage) return;
                    currentPage = page;
                    renderPage();
                });

                li.appendChild(a);
                paginationContainer.appendChild(li);
            }
        }

        function renderPage() {
            if (!productos.length) {
                return;
            }
            productosList.innerHTML = '';

            const start = (currentPage - 1) * pageSize;
            const end = start + pageSize;
            const pageItems = productos.slice(start, end);

            pageItems.forEach(producto => {
                const item = document.createElement('div');
                item.className = 'list-group-item';
                item.innerHTML = `
                    <div class="d-flex col-md-12 align-items-center ">
                        <div class="col-md-4">
                            <strong>${producto.nombre}</strong><br>
                            <small>Sugerido: ${producto.stock_total}</small>
                        </div>
                        <div class="input-group col-md-6">
                            <div class="input-group-prepend">
                                <button type="button" class="btn btn-sm btn-outline-secondary decrement" data-id="${producto.id_producto}">-</button>
                            </div>
                            <input type="number" class="form-control cantidad" data-id="${producto.id_producto}" value="0" min="0">
                            <div class="input-group-append">
                                <button type="button" class="btn btn-sm btn-outline-secondary increment" data-id="${producto.id_producto}">+</button>
                            </div>
                        </div>
                        <button type="button" class="btn btn-sm btn-success aplicar col-md-2" data-id="${producto.id_producto}" data-nombre="${producto.nombre}">Aplicar</button>
                    </div>
                `;

                productosList.appendChild(item);

                const inputCantidad = item.querySelector('.cantidad');
                const btnIncrement = item.querySelector('.increment');
                const btnDecrement = item.querySelector('.decrement');
                const btnAplicar   = item.querySelector('.aplicar');

                btnIncrement.addEventListener('click', function(e) {
                    e.preventDefault();
                    inputCantidad.value = parseInt(inputCantidad.value || 0) + 1;
                });

                btnDecrement.addEventListener('click', function(e) {
                    e.preventDefault();
                    inputCantidad.value = Math.max(0, parseInt(inputCantidad.value || 0) - 1);
                });

                btnAplicar.addEventListener('click', function(e) {
                    e.preventDefault();

                    const id = this.getAttribute('data-id');
                    const nombre = this.getAttribute('data-nombre');
                    const cantidad = parseInt(inputCantidad.value);

                    if (cantidad > 0) {
                        const existente = productosSeleccionados.querySelector(`li[data-id="${id}"]`);

                        if (existente) {
                            const cantidadSpan = existente.querySelector('.cantidad-span');
                            const nuevaCantidad = parseInt(cantidadSpan.textContent) + cantidad;
                            cantidadSpan.textContent = nuevaCantidad;
                        } else {
                            const seleccionado = document.createElement('li');
                            seleccionado.className = 'list-group-item d-flex justify-content-between align-items-center';
                            seleccionado.setAttribute('data-id', id);
                            seleccionado.innerHTML = `
                                <span>${nombre} x<span class="cantidad-span">${cantidad}</span></span>
                                <button type="button" class="btn btn-sm btn-danger" data-id="${id}">Quitar</button>
                            `;
                            productosSeleccionados.appendChild(seleccionado);

                            seleccionado.querySelector('button').addEventListener('click', function(e) {
                                e.preventDefault();
                                this.parentElement.remove();
                            });
                        }
                        inputCantidad.value = 0;
                    }
                });
            });

            renderPagination();
        }

        loadProductos();
        renderPage();

        document.getElementById('guardarInsumos').addEventListener('click', function(e) {
            e.preventDefault();
            const seleccionados = [];
            productosSeleccionados.querySelectorAll('li').forEach(item => {
                seleccionados.push(item.querySelector('span').textContent);
            });
            insumosInput.value = seleccionados.join(', ');
            $('#insumosModal').modal('hide');
        });
        
    });
</script>
@endsection
