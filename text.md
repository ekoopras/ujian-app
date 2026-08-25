- Model: User
    - Kolom: id, name, email, password, role, mapel_id,
    - relasi
      public function mapel()
      {
      return $this->belongsToMany(Mapel::class);
      }

- Model: Kelase
    - Kolom: id, name, slug

- Model Mapel
    - Kolom: id, name, slug

- Model TahunAjaran
    - kolom: id, tahun, semester, is_active
