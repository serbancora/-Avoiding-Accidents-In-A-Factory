<?php
// accidente_repo.php (SQL Server - sqlsrv)

function fetch_all($stmt): array {
    $rows = [];
    while ($r = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        $rows[] = $r;
    }
    return $rows;
}

function get_departamente($conn): array {
    $sql = "select id_departament, nume_departament from Departament order by nume_departament";
    $stmt = sqlsrv_query($conn, $sql);
    if ($stmt === false) return [];
    return fetch_all($stmt);
}

function get_angajati($conn, $dept_id = null): array {
    if ($dept_id === null) {
        $sql = "
            select
                a.id_angajat,
                concat(a.nume,' ',a.prenume) as nume_complet,
                d.nume_departament
            from Angajat a
            join Departament d on d.id_departament = a.id_departament
            order by a.nume, a.prenume
        ";
        $stmt = sqlsrv_query($conn, $sql);
        if ($stmt === false) return [];
        return fetch_all($stmt);
    }

    $sql = "
        select
            a.id_angajat,
            concat(a.nume,' ',a.prenume) as nume_complet,
            d.nume_departament
        from Angajat a
        join Departament d on d.id_departament = a.id_departament
        where a.id_departament = ?
        order by a.nume, a.prenume
    ";
    $stmt = sqlsrv_query($conn, $sql, [(int)$dept_id]);
    if ($stmt === false) return [];
    return fetch_all($stmt);
}

function get_accidente($conn, string $rol, $dept_id, array $filters): array {
    // NOTE: STRING_AGG necesita SQL Server 2017+.
    // Daca esti pe o versiune mai veche, zi-mi si iti fac varianta cu FOR XML PATH.

    $sql = "
        select
          a.id_accident,
          a.data_accident,
          a.ora_accident,
          a.locatie,
          a.descriere,
          a.gravitate,
          u.username as raportat_de,

          string_agg(concat(ang.nume, ' ', ang.prenume), ', ') as angajati_implicati,
          string_agg(d.nume_departament, ', ') as departamente_implicate

        from Accident a
        join Utilizator u on u.id_utilizator = a.id_utilizator
        left join Angajat_Accident aa on aa.id_accident = a.id_accident
        left join Angajat ang on ang.id_angajat = aa.id_angajat
        left join Departament d on d.id_departament = ang.id_departament
        where 1=1
    ";

    $params = [];

    // Manager: doar accidente ce au cel putin un angajat din dept lui
    if ($rol === 'manager') {
        $sql .= "
          and exists (
            select 1
            from Angajat_Accident aa2
            join Angajat ang2 on ang2.id_angajat = aa2.id_angajat
            where aa2.id_accident = a.id_accident
              and ang2.id_departament = ?
          )
        ";
        $params[] = (int)($dept_id ?? -1);
    }

    // q: locatie/descriere
    if (!empty($filters['q'])) {
        $sql .= " and (a.locatie like ? or a.descriere like ?) ";
        $like = "%" . $filters['q'] . "%";
        $params[] = $like;
        $params[] = $like;
    }

    // from/to
    if (!empty($filters['from'])) {
        $sql .= " and a.data_accident >= ? ";
        $params[] = $filters['from'];
    }
    if (!empty($filters['to'])) {
        $sql .= " and a.data_accident <= ? ";
        $params[] = $filters['to'];
    }

    // gravitate: minor/grav/mortal
    if (!empty($filters['gravitate'])) {
        $sql .= " and a.gravitate = ? ";
        $params[] = $filters['gravitate'];
    }

    // departament (admin/ssm)
    if (($rol === 'admin' || $rol === 'ssm') && !empty($filters['departament'])) {
        $sql .= "
          and exists (
            select 1
            from Angajat_Accident aa3
            join Angajat ang3 on ang3.id_angajat = aa3.id_angajat
            where aa3.id_accident = a.id_accident
              and ang3.id_departament = ?
          )
        ";
        $params[] = (int)$filters['departament'];
    }

    $sql .= "
        group by
          a.id_accident, a.data_accident, a.ora_accident, a.locatie, a.descriere, a.gravitate, u.username
        order by a.data_accident desc, a.ora_accident desc
    ";

    $stmt = sqlsrv_query($conn, $sql, $params);
    if ($stmt === false) {
        echo "<pre>";
        print_r(sqlsrv_errors());
        echo "</pre>";
        return [];
    }
    return fetch_all($stmt);
}

function get_accident_by_id($conn, int $id_accident): ?array {
    $sql = "
        select id_accident, data_accident, ora_accident, locatie, descriere, gravitate
        from Accident
        where id_accident = ?
    ";
    $stmt = sqlsrv_query($conn, $sql, [$id_accident]);
    if ($stmt === false) return null;

    $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
    return $row ?: null;
}

function get_accident_angajati($conn, int $id_accident): array {
    $sql = "
        select
            aa.id_angajat,
            concat(a.nume,' ',a.prenume) as nume_complet,
            a.id_departament,
            d.nume_departament,
            aa.consecinta,
            aa.concediu_medical_zile
        from Angajat_Accident aa
        join Angajat a on a.id_angajat = aa.id_angajat
        join Departament d on d.id_departament = a.id_departament
        where aa.id_accident = ?
        order by a.nume, a.prenume
    ";
    $stmt = sqlsrv_query($conn, $sql, [$id_accident]);
    if ($stmt === false) return [];
    return fetch_all($stmt);
}
