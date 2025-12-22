@if ($errors->any())
    <div class="alert alert-danger">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

@php
    $paqueteEnEdicion = isset($paquete) && $paquete->exists;
@endphp

@php
    // === ALMACÉN fallback (exact values you requested) ===
    $ALMACEN_LAT = -17.722196014285355;
    $ALMACEN_LNG = -63.17460540787094;

    // Values from old() first (validation redirect), else from model
    $latValue  = old('latitud',  $paquete->latitud  ?? null);
    $lngValue  = old('longitud', $paquete->longitud ?? null);
    $zonaValue = old('zona',     $paquete->zona     ?? null);

    if ((!is_numeric($latValue) || !is_numeric($lngValue)) && !empty($paquete->ubicacion_actual)) {
        if (preg_match('/\(?\s*(-?\d+(?:\.\d+)?)\s*,\s*(-?\d+(?:\.\d+)?)\s*\)?/', $paquete->ubicacion_actual, $m)) {
            $latValue = $m[1];
            $lngValue = $m[2];
        }
    }

    $estadoSeleccionadoId = old('estado_id', $paquete->estado_id ?? null);
    $estadoSeleccionadoNombre = $estadoSeleccionadoId
        ? ($estados[$estadoSeleccionadoId] ?? '')
        : (optional($paquete->estado)->nombre_estado ?? '');

    $esArmado = strcasecmp(trim($estadoSeleccionadoNombre), 'Armado') === 0;

    $coordsMissing = !(is_numeric($latValue) && is_numeric($lngValue));
    $zonaMissing   = empty($zonaValue);

    if ($esArmado && ($coordsMissing || $zonaMissing)) {
        $defaultLat  = $ALMACEN_LAT;
        $defaultLng  = $ALMACEN_LNG;
        $defaultZoom = 14;
        $zonaValue   = 'Almacen de Donaciones';
    } else {
        $defaultLat  = is_numeric($latValue) ? (float)$latValue : $ALMACEN_LAT;
        $defaultLng  = is_numeric($lngValue) ? (float)$lngValue : $ALMACEN_LNG;

        $hasCoords   = is_numeric($latValue) && is_numeric($lngValue);
        $defaultZoom = $hasCoords ? 13 : 6;
    }
@endphp


<div class="row padding-1 p-1">
  <div class="col-md-12">

    <div class="form-group mb-2 mb20">
      <label for="id_solicitud" class="form-label">Solicitud</label>
      <select name="id_solicitud" id="id_solicitud"
              class="form-control @error('id_solicitud') is-invalid @enderror" required>
        <option value="">-- Seleccione --</option>
        @foreach($solicitudes as $s)
          @php
            $soli = optional($s->solicitante);
            $dest = optional($s->destino);
            $label = sprintf('Nº%d · %s %s · %s · %s',
              $s->id_solicitud,
              $soli->nombre ?? '—',
              $soli->apellido ?? '',
              $dest->comunidad ?? '—',
              $s->codigo_seguimiento ?? '—'
            );
          @endphp
          <option value="{{ $s->id_solicitud }}"
            {{ (string)old('id_solicitud', $paquete->id_solicitud ?? '') === (string)$s->id_solicitud ? 'selected' : '' }}>
            {{ $label }}
          </option>
        @endforeach
      </select>
      {!! $errors->first('id_solicitud', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
    </div>

    <div class="form-group mb-2 mb20">
      <label for="estado_id" class="form-label">Estado</label>
      <select name="estado_id" id="estado_id"
              class="form-control @error('estado_id') is-invalid @enderror" required>
        <option value="">-- Seleccione --</option>
        @foreach($estados as $id => $nombre)
          @php
            $esPendiente = $paqueteEnEdicion && isset($paquete->estado) && strcasecmp($paquete->estado->nombre_estado ?? '', 'Pendiente') === 0;
            $esArmado = $paqueteEnEdicion && strcasecmp($nombre, 'Armado') === 0;
            $esEnCamino = $paqueteEnEdicion && strcasecmp($nombre, 'En camino') === 0;
            $esEntregado = $paqueteEnEdicion && strcasecmp($nombre, 'Entregado') === 0;
          @endphp
          @if($paqueteEnEdicion && (strcasecmp($nombre, 'Pendiente') === 0 || $esArmado))
            @continue
          @endif
          @if($esPendiente && ($esEnCamino || $esEntregado))
            @continue
          @endif
          <option value="{{ $id }}"
            {{ (string)old('estado_id', $paquete->estado_id ?? '') === (string)$id ? 'selected' : '' }}>
            {{ $nombre }}
          </option>
        @endforeach
      </select>
      {!! $errors->first('estado_id', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
    </div>

    <div class="card mt-3 mb-3">
      <div class="card-header">
        <strong>Datos de Transporte</strong>
      </div>
      <div class="card-body">

        <div class="form-group mb-2 mb20">
          <label for="id_conductor" class="form-label">Conductor asignado</label>
          <select name="id_conductor" id="id_conductor"
                  class="form-control @error('id_conductor') is-invalid @enderror">
            <option value="">-- Sin asignar / Seleccionar --</option>
            @foreach($conductores as $c)
              @php
                  $nombreConductor = trim(($c->nombre ?? '').' '.($c->apellido ?? ''));
              @endphp
              <option value="{{ $c->conductor_id }}"
                {{ (string) old('id_conductor', $paquete->id_conductor ?? '') === (string) $c->conductor_id ? 'selected' : '' }}>
                {{ $nombreConductor ?: 'Sin nombre' }} (CI {{ $c->ci ?? '—' }})
              </option>
            @endforeach
          </select>
          {!! $errors->first('id_conductor', '<div class="invalid-feedback"><strong>:message</strong></div>') !!}
          <small class="form-text text-muted">
            ¿No existe el conductor? 
            <a href="#" data-toggle="modal" data-target="#modalConductor">
                Crear nuevo conductor
            </a>
          </small>
        </div>
        <script>
        document.addEventListener('DOMContentLoaded', function() {
          const vehiculoSelect = document.getElementById('id_vehiculo');
          const conductorSelect = document.getElementById('id_conductor');
          if (!vehiculoSelect || !conductorSelect) return;

          
          const vehiculoConductorEnCamino = @json(\App\Models\Paquete::whereHas('estado', function($q){ $q->whereRaw('LOWER(nombre_estado) = ?', ['en camino']); })
            ->whereNotNull('id_vehiculo')
            ->whereNotNull('id_conductor')
            ->get()
            ->mapWithKeys(function($p){ return [$p->id_vehiculo => $p->id_conductor]; })
          );

          vehiculoSelect.addEventListener('change', function() {
            const selectedVehiculo = this.value;
            if (vehiculoConductorEnCamino[selectedVehiculo]) {
              conductorSelect.value = vehiculoConductorEnCamino[selectedVehiculo];
              conductorSelect.setAttribute('readonly', 'readonly');
            } else {
              conductorSelect.value = '';
              conductorSelect.removeAttribute('readonly');
            }
          });

          // Al cargar, si el vehículo ya tiene conductor en camino, bloquear selección
          if (vehiculoSelect.value && vehiculoConductorEnCamino[vehiculoSelect.value]) {
            conductorSelect.value = vehiculoConductorEnCamino[vehiculoSelect.value];
            conductorSelect.setAttribute('readonly', 'readonly');
          }
        });
        </script>

        <div class="form-group mb-2 mb20">
          <label for="id_vehiculo" class="form-label">Vehículo asignado</label>
          <select name="id_vehiculo" id="id_vehiculo"
                  class="form-control @error('id_vehiculo') is-invalid @enderror">
            <option value="">-- Sin asignar / Seleccionar --</option>
            @foreach($vehiculos as $v)
              <option value="{{ $v->id_vehiculo }}"
                {{ (string) old('id_vehiculo', $paquete->id_vehiculo ?? '') === (string) $v->id_vehiculo ? 'selected' : '' }}>
                {{ $v->placa ?? 'Sin placa' }}
              </option>
            @endforeach
          </select>
          {!! $errors->first('id_vehiculo', '<div class="invalid-feedback"><strong>:message</strong></div>') !!}
          <small class="form-text text-muted">
            ¿No existe el vehículo? 
            <a href="#" data-toggle="modal" data-target="#modalVehiculo">
                Crear nuevo vehículo
            </a>
          </small>
        </div>

      </div>
    </div>


    <div class="form-group mb-2 mb20">
        <label for="imagen" class="form-label">Imagen de Evidencia</label>
        <input type="file" name="imagen" id="imagen"
              accept="image/*"
              class="form-control @error('imagen') is-invalid @enderror">
        <div class="mt-3" id="preview-container">
            @if($paquete->imagen)
                <img id="preview-imagen" src="{{ asset('storage/' . $paquete->imagen) }}"
                    alt="Imagen no seleccionada"
                    class="img-thumbnail"
                    style="max-height:200px; border-radius:8px;">
            @else
                <img id="preview-imagen" src="#" alt="Vista previa"
                    class="img-thumbnail d-none"
                    style="max-height:200px; border-radius:8px;">
            @endif
        </div>
    </div>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const inputImagen   = document.getElementById('imagen');
        const previewImagen = document.getElementById('preview-imagen');

        if (!inputImagen || !previewImagen) return;

        inputImagen.addEventListener('change', function (e) {
            const file = e.target.files[0];
            if (!file || !file.type.startsWith('image/')) return;

            const reader = new FileReader();
            const img    = new Image();

            reader.onload = function(ev) {
                img.onload = function() {
                    const maxWidth  = 1600;
                    const maxHeight = 1600;

                    let width  = img.width;
                    let height = img.height;

                    const scale = Math.min(
                        maxWidth  / width,
                        maxHeight / height,
                        1
                    );
                    width  = Math.round(width * scale);
                    height = Math.round(height * scale);

                    const canvas = document.createElement('canvas');
                    canvas.width  = width;
                    canvas.height = height;
                    const ctx = canvas.getContext('2d');
                    ctx.drawImage(img, 0, 0, width, height);

                    canvas.toBlob(function (blob) {
                        if (!blob) return;

                        const compressedFile = new File(
                            [blob],
                            file.name.replace(/\.\w+$/, '.jpg'),
                            { type: 'image/jpeg', lastModified: Date.now() }
                        );

                        const dt = new DataTransfer();
                        dt.items.add(compressedFile);
                        inputImagen.files = dt.files;

                        const url = URL.createObjectURL(blob);
                        previewImagen.src = url;
                        previewImagen.classList.remove('d-none');
                    }, 'image/jpeg', 0.7);
                };

                img.src = ev.target.result;
            };

            reader.readAsDataURL(file);
        });
    });
    </script>


    <div id="geo-alert" class="alert alert-warning d-none" role="alert"></div>
    <div class="card mt-3 mb-3">
      <div class="card-header">
        <strong>Ubicación Actual</strong>
      </div>
      <div class="card-body">
        <div class="row mb-3">
          <div class="col-md-12">
            <div class="form-group mb-2 mb20">
              <label for="mapa-ubicacion-paquete">Seleccione la Ubicación en el Mapa</label>
              <div id="mapa-ubicacion-paquete" style="height: 400px; width: 100%; border: 1px solid #ddd; border-radius: 4px;"></div>
              <p class="form-text text-muted">Su ubicación se detecta automáticamente, no es posible editar este punto por seguridad.</p>
              {!! $errors->first('latitud', '<div class="invalid-feedback d-block"><strong>:message</strong></div>') !!}
              {!! $errors->first('longitud', '<div class="invalid-feedback d-block"><strong>:message</strong></div>') !!}
            </div>
          </div>
        </div>

        <div class="row">
          <div class="col-md-6">
            <div class="form-group mb-2 mb20">
              <label for="zona" class="form-label">Dirección / Zona (detectada automáticamente)</label>
              <input type="text" name="zona" id="zona"
                    class="form-control @error('zona') is-invalid @enderror"
                    value="{{ $zonaValue }}"
                    placeholder="Se completará automáticamente desde su ubicación"
                    readonly>
              {!! $errors->first('zona', '<div class="invalid-feedback"><strong>:message</strong></div>') !!}
            </div>
          </div>
          <div class="col-md-6">
            <div class="form-group mb-2 mb20">
              <label for="provincia_actual" class="form-label">Provincia (detectada automáticamente)</label>
              <input type="text" id="provincia_actual"
                    class="form-control"
                    value=""
                    readonly>
            </div>
          </div>

          <div class="col-md-4" hidden>
            <div class="form-group mb-2 mb20">
              <label for="latitud" class="form-label">Latitud</label>
              <input type="number" step="any" name="latitud" id="latitud" readonly
                    class="form-control @error('latitud') is-invalid @enderror"
                    value="{{ old('latitud', $defaultLat) }}"
                    placeholder="-17.7833">
              {!! $errors->first('latitud', '<div class="invalid-feedback"><strong>:message</strong></div>') !!}
            </div>
          </div>

          <div class="col-md-4" hidden>
            <div class="form-group mb-2 mb20">
              <label for="longitud" class="form-label">Longitud</label>
              <input type="number" step="any" name="longitud" id="longitud" readonly
                    class="form-control @error('longitud') is-invalid @enderror"
                    value="{{ old('longitud', $defaultLng) }}"
                    placeholder="-63.1821">
              {!! $errors->first('longitud', '<div class="invalid-feedback"><strong>:message</strong></div>') !!}
            </div>
          </div>
        </div>

        <div class="form-group mb-2 mb20" hidden>
          <label for="ubicacion_actual" class="form-label">Ubicación (generada automáticamente)</label>
          <input type="text" name="ubicacion_actual" id="ubicacion_actual"
                class="form-control"
                value="{{ old('ubicacion_actual', $paquete->ubicacion_actual) }}"
                readonly
                placeholder="Se generará al guardar">
        </div>
      </div>
    </div>


    <div class="row">
      <div class="col-md-6">
        <div class="form-group mb-2 mb20">
          <label class="form-label">Código</label>
          <input type="text" class="form-control" value="{{ $paquete->solicitud->codigo_seguimiento ?? 'Se generará al guardar' }}" disabled>
        </div>
      </div>
    </div>

    <div class="text-right mt-2">
      <button type="submit" class="btn btn-primary" id="btn-submit-paquete">
          Guardar
      </button>
      <a href="{{ route('paquete.index') }}" class="btn btn-secondary">Volver</a>
    </div>

  </div>
</div>



@php
    $latValue = old('latitud');
    $lngValue = old('longitud');

    if (!$latValue && isset($paquete->ubicacion_actual) && $paquete->ubicacion_actual) {
        if (strpos($paquete->ubicacion_actual, ',') !== false) {
            $parts = explode(',', $paquete->ubicacion_actual);
            $latValue = trim($parts[0] ?? null);
            $lngValue = trim($parts[1] ?? null);
        }
    }

    $estadoSeleccionadoId = old('estado_id', $paquete->estado_id ?? null);
    $estadoSeleccionadoNombre = $estadoSeleccionadoId ? ($estados[$estadoSeleccionadoId] ?? '') : '';
    $esArmado = strcasecmp($estadoSeleccionadoNombre, 'Armado') === 0;

    $ALMACEN_LAT = -17.722196014285355;
    $ALMACEN_LNG = -63.17460540787094;

    $zonaValue = old('zona', $paquete->zona ?? null);

    $coordsMissing = !(is_numeric($latValue) && is_numeric($lngValue));
    $zonaMissing   = empty($zonaValue);

    if ($esArmado && ($coordsMissing || $zonaMissing)) {
        $defaultLat = $ALMACEN_LAT;
        $defaultLng = $ALMACEN_LNG;
        $defaultZoom = 14;
        $zonaValue = 'Almacen de Donaciones';
    } else {
        $defaultLat = (is_numeric($latValue) && $latValue) ? (float) $latValue : $ALMACEN_LAT;
        $defaultLng = (is_numeric($lngValue) && $lngValue) ? (float) $lngValue : $ALMACEN_LNG;
        $hasCoords  = (is_numeric($latValue) && $latValue && is_numeric($lngValue) && $lngValue);
        $defaultZoom = $hasCoords ? 13 : 6;
    }
@endphp

<script>
(function() {

  function initMap() {
    if (typeof L === 'undefined') {
      console.warn("Leaflet no está cargado. Reintentando...");
      setTimeout(initMap, 100);
      return;
    }

    const mapContainer = document.getElementById('mapa-ubicacion-paquete');
    if (!mapContainer) return;

    const FALLBACK_LAT = -17.722213878615346;
    const FALLBACK_LNG = -63.17462682731379;

    const defaultLat  = Number("{{ $defaultLat }}");
    const defaultLng  = Number("{{ $defaultLng }}");
    const defaultZoom = Number("{{ $defaultZoom }}");

    const GEO_REVERSE_URL = @json(url('/api/geo/reverse'));

    const map = L.map('mapa-ubicacion-paquete', {
      zoomControl: true,
      dragging: false,
      scrollWheelZoom: false,
      doubleClickZoom: false,
      boxZoom: false,
      touchZoom: false,
      keyboard: false,
      tap: false,
    }).setView([defaultLat, defaultLng], defaultZoom);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
      attribution: '&copy; OpenStreetMap contributors',
      maxZoom: 19,
    }).addTo(map);

    let marker = null;

    const latInput       = document.getElementById('latitud');
    const lngInput       = document.getElementById('longitud');
    const direccionInput = document.getElementById('zona');
    const provinciaInput = document.getElementById('provincia_actual');
    const estadoSelect   = document.getElementById('estado_id');

    function isArmadoSelected() {
      if (!estadoSelect) return false;
      const txt = (estadoSelect.options[estadoSelect.selectedIndex]?.text || '').trim();
      return txt.toLowerCase() === 'armado';
    }

    function setFixedAlmacen() {
      if (direccionInput) direccionInput.value = 'Almacen de Donaciones';
      if (provinciaInput) provinciaInput.value = '';
    }

    function reverseGeocode(lat, lng) {
      if (!direccionInput && !provinciaInput) return;

      if (isArmadoSelected()) {
        setFixedAlmacen();
        return;
      }

      const url =
        `${GEO_REVERSE_URL}?lat=${encodeURIComponent(lat)}&lng=${encodeURIComponent(lng)}`;

      fetch(url, { headers: { 'Accept': 'application/json' } })
        .then(r => {
          if (!r.ok) throw new Error('Geo reverse HTTP ' + r.status);
          return r.json();
        })
        .then(data => {
          const addr = data?.address || {};

          let dir = addr.road ?? '';
          if (addr.house_number)  dir += (dir ? ' ' : '') + addr.house_number;
          if (addr.neighbourhood) dir += (dir ? ', ' : '') + addr.neighbourhood;
          if (addr.suburb)        dir += (dir ? ', ' : '') + addr.suburb;
          if (addr.city || addr.town) dir += (dir ? ', ' : '') + (addr.city || addr.town);

          if (direccionInput) {
            direccionInput.value = dir || data.display_name || `${Number(lat).toFixed(6)}, ${Number(lng).toFixed(6)}`;
          }

          if (provinciaInput) {
            provinciaInput.value =
              addr.village ?? addr.town ?? addr.city ?? addr.municipality ?? addr.state ?? addr.region ?? addr.county ?? '';
          }
        })
        .catch(err => {
          console.warn('Error reverse geocoding (backend):', err);
          if (direccionInput) direccionInput.value = `${Number(lat).toFixed(6)}, ${Number(lng).toFixed(6)}`;
          if (provinciaInput) provinciaInput.value = '';
        });
    }

    let reverseTimeout = null;
    let lastRG = { lat: null, lng: null };

    function reverseGeocodeThrottled(lat, lng) {
      if (isArmadoSelected()) {
        setFixedAlmacen();
        return;
      }

      const keyLat = Number(lat).toFixed(6);
      const keyLng = Number(lng).toFixed(6);
      if (lastRG.lat === keyLat && lastRG.lng === keyLng) return;
      lastRG = { lat: keyLat, lng: keyLng };

      clearTimeout(reverseTimeout);
      reverseTimeout = setTimeout(() => reverseGeocode(lat, lng), 450);
    }

    function setMarker(lat, lng, opts = { doReverse: true }) {
      if (latInput && lngInput) {
        latInput.value = Number(lat).toFixed(6);
        lngInput.value = Number(lng).toFixed(6);
      }

      if (!marker) {
        marker = L.marker([lat, lng], { draggable: false }).addTo(map);
      } else {
        marker.setLatLng([lat, lng]);
      }

      map.setView([lat, lng], 15);

      if (opts.doReverse) reverseGeocodeThrottled(lat, lng);
    }

    if (isArmadoSelected()) {
      setMarker(FALLBACK_LAT, FALLBACK_LNG, { doReverse: false });
      setFixedAlmacen();
    } else if (navigator.geolocation) {
      navigator.geolocation.getCurrentPosition(
        function(position) {
          setMarker(position.coords.latitude, position.coords.longitude, { doReverse: true });
        },
        function(error) {
          console.warn("Error o permiso denegado en geolocalización:", error);
          setMarker(FALLBACK_LAT, FALLBACK_LNG, { doReverse: true });

          const geoAlert = document.getElementById("geo-alert");
          if (geoAlert) {
            geoAlert.classList.remove("d-none");
            geoAlert.innerHTML = `Se usó una ubicación de referencia para registrar la posición del paquete.`;
          }
        },
        { enableHighAccuracy: true, timeout: 8000 }
      );
    } else {
      setMarker(FALLBACK_LAT, FALLBACK_LNG, { doReverse: true });
    }
  }

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", initMap);
  } else {
    initMap();
  }

})();
</script>
