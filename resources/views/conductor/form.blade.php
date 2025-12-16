<div class="row padding-1 p-1">
    <div class="col-md-12">
        <div class="form-group mb-2 mb20">
            <label for="nombre" class="form-label">{{ __('Nombre') }}</label>
            <input type="text" name="nombre" class="form-control @error('nombre') is-invalid @enderror" value="{{ old('nombre', $conductor?->nombre) }}" id="nombre" placeholder="Nombre"
                   pattern="[A-Za-zÁÉÍÓÚáéíóúÑñ ]+" title="Solo letras y espacios" oninput="this.value = this.value.replace(/[^A-Za-zÁÉÍÓÚáéíóúÑñ ]/g, '')">
            {!! $errors->first('nombre', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        <div class="form-group mb-2 mb20">
            <label for="apellido" class="form-label">{{ __('Apellido') }}</label>
            <input type="text" name="apellido" class="form-control @error('apellido') is-invalid @enderror" value="{{ old('apellido', $conductor?->apellido) }}" id="apellido" placeholder="Apellido"
                   pattern="[A-Za-zÁÉÍÓÚáéíóúÑñ ]+" title="Solo letras y espacios" oninput="this.value = this.value.replace(/[^A-Za-zÁÉÍÓÚáéíóúÑñ ]/g, '')">
            {!! $errors->first('apellido', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        <div class="form-group mb-2 mb20">
            <label for="fecha_nacimiento" class="form-label">Fecha de Nacimiento</label>
            <input type="date" name="fecha_nacimiento" class="form-control @error('fecha_nacimiento') is-invalid @enderror" value="{{ old('fecha_nacimiento', $conductor?->fecha_nacimiento) }}" id="fecha_nacimiento" max="{{ \Carbon\Carbon::now()->subYears(18)->format('Y-m-d') }}">
            {!! $errors->first('fecha_nacimiento', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        <div class="form-group mb-2 mb20">
            <label for="ci" class="form-label">{{ __('Carnet de Identidad') }}</label>
            <input type="text" name="ci" class="form-control @error('ci') is-invalid @enderror" value="{{ old('ci', $conductor?->ci) }}" id="ci" placeholder="Ci">
            {!! $errors->first('ci', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        <div class="form-group mb-2 mb20">
            <label for="celular" class="form-label">{{ __('Número de Celular') }}</label>
            <input type="text" name="celular" class="form-control @error('celular') is-invalid @enderror" value="{{ old('celular', $conductor?->celular) }}" id="celular" placeholder="Ej. +591 70000000"
                   pattern="^[0-9+\-() ]+$" title="Solo números y símbolos (+ - ( ) espacios)"
                   oninput="this.value = this.value.replace(/[^0-9+\-() ]/g, '')">
            {!! $errors->first('celular', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        <div class="form-group mb-2 mb20">
            <label for="id_licencia" class="form-label">Tipo de Licencia</label>
            <select name="id_licencia" id="id_licencia" class="form-select @error('id_licencia') is-invalid @enderror">
                <option value="">Seleccione una licencia</option>
                @foreach($licencia as $lic)
                    <option value="{{ $lic->id_licencia }}"
                        {{ old('id_licencia', $conductor?->id_licencia) == $lic->id_licencia ? 'selected' : '' }}>
                        {{ $lic->licencia }}
                    </option>
                @endforeach
            </select>
            {!! $errors->first('id_licencia', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>

    </div>
    <div class="col-md-12 mt20 mt-2">
        <button type="submit" class="btn btn-primary">{{ __('Guardar') }}</button>
    </div>
</div>