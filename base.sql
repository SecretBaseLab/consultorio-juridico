-- create database consultorio;

CREATE TABLE cliente(
    cedula varchar (10) not null PRIMARY KEY,
    nombres varchar (50) not null,
    apellidos varchar (50) not null,
    direccion varchar (100) not null,
    otros text,
    created_at DATETIME COMMENT "fecha del ingreso al sistema de un nuevo cliente",
    updated_at DATETIME
);

    create table telefono_cliente (
        numero varchar (10) not null,
        cedula varchar (10) not null,
        FOREIGN KEY (cedula) references cliente(cedula),
        PRIMARY KEY (numero, cedula),
        created_at DATETIME,
        updated_at DATETIME 
    );

    create table correo_cliente (
        correo varchar (100) not null,
        cedula varchar (10) not null,
        FOREIGN KEY (cedula) references cliente(cedula),
        PRIMARY KEY (correo, cedula),
        created_at DATETIME,
        updated_at DATETIME
    );

create table expediente_local (
    numero_expediente int UNSIGNED AUTO_INCREMENT PRIMARY KEY, 
    tipo varchar (50) not null check (tipo="Civil" or tipo="Penal" or tipo="Contencioso"),
    created_at DATETIME COMMENT "fecha_inicio_expediente",
    updated_at DATETIME,
    otros varchar (100),
    cedula varchar (10) not null,
    FOREIGN KEY (cedula) references cliente(cedula)
);
    CREATE TABLE estado_expediente(
        id_estado int UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        estado varchar (50) default "En curso" CHECK (estado="Finalizado" or estado="En curso" or estado="Abandonado"),
        created_at DATETIME COMMENT "fecha del cambio de estado",
        updated_at DATETIME,
        numero_expediente int UNSIGNED not null,
        FOREIGN KEY (numero_expediente) REFERENCES expediente_local(numero_expediente)
    );
    CREATE TABLE notas_expediente (
        id_notas int UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        created_at DATETIME COMMENT "fecha de la nueva nota",
        updated_at DATETIME,
        descripcion text,
        numero_expediente int UNSIGNED,
        FOREIGN KEY (numero_expediente) REFERENCES expediente_local(numero_expediente)
    );

    create table adjuntos_expediente (
        id_archivo int UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        file_name text not null,
        file_name_hash text not null,
        extension varchar(10) not null,
        created_at DATETIME COMMENT "fecha en que se sube un nuevo archivo al expediente",
        updated_at DATETIME,
        numero_expediente int UNSIGNED not null,
        FOREIGN KEY (numero_expediente) REFERENCES expediente_local(numero_expediente)
    );

CREATE TABLE unidad_fiscal (
    id_unidad_fiscal int UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre_unidad varchar(150),
    created_at DATETIME,
    updated_at DATETIME
);

CREATE TABLE instruccion_fiscal (
    numero_instruccion varchar (15) not null PRIMARY KEY,
    created_at DATETIME COMMENT "fecha del inicio de la instrccion fiscal",
    updated_at DATETIME,
    denunciante varchar(100) not null,
    denunciado varchar(100) not null,
    fiscal varchar(100) not null,
    numero_expediente int UNSIGNED not null,
    FOREIGN KEY (numero_expediente) REFERENCES expediente_local(numero_expediente),
    id_unidad_fiscal int UNSIGNED not null,
    FOREIGN KEY (id_unidad_fiscal) REFERENCES unidad_fiscal(id_unidad_fiscal)
);

    CREATE TABLE observaciones_instruccion (
        id_observacion int UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        created_at DATETIME COMMENT "fecha de una nueva observacion de la intruccion fiscal",
        updated_at DATETIME,
        descripcion text not null,
        numero_instruccion varchar (15) not null,
        FOREIGN KEY (numero_instruccion) REFERENCES instruccion_fiscal(numero_instruccion)
    );

CREATE TABLE movimiento_instruccion (
    id_movimiento int UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    created_at DATETIME COMMENT "fecha del movimiento de la instruccion",
    updated_at DATETIME,
    detalle text not null,
    numero_instruccion varchar (15) not null,
    FOREIGN KEY (numero_instruccion) REFERENCES instruccion_fiscal(numero_instruccion)
);

    create table adjuntos_movimiento_instruccion (
        id_archivo int UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        file_name text not null,
        file_name_hash text not null,
        extension varchar(10) not null,
        created_at DATETIME,
        updated_at DATETIME,
        id_movimiento int UNSIGNED not null,
        FOREIGN KEY (id_movimiento) REFERENCES movimiento_instruccion(id_movimiento)
    );

CREATE TABLE versiones (
    id_version int UNSIGNED AUTO_INCREMENT PRIMARY KEY, 
    created_at DATETIME COMMENT "fecha de una nueva version",
    updated_at DATETIME,
    tipo varchar (10) not null check(tipo="Presencial" or tipo="Virtual"),
    observaciones text,
    numero_instruccion varchar (15) not null,
    FOREIGN KEY (numero_instruccion) REFERENCES instruccion_fiscal(numero_instruccion)
);

    create table version_presencial(
        direccion varchar(150) not null,
        id_version int UNSIGNED not null,
        FOREIGN KEY (id_version) REFERENCES versiones(id_version),
        PRIMARY KEY(id_version),
        created_at DATETIME,
        updated_at DATETIME
    );

    create table version_virtual(
        direccion_web varchar(150) not null,
        id_version int UNSIGNED not null,
        FOREIGN KEY (id_version) REFERENCES versiones(id_version),
        PRIMARY KEY(id_version),
        created_at DATETIME,
        updated_at DATETIME
    );

create table unidad_judicial(
    id_unidad_judicial int UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre_unidad varchar(150) not null,
    created_at DATETIME,
    updated_at DATETIME
);

create table juicio(
    numero_juicio varchar (100) not null PRIMARY KEY,
    created_at DATETIME COMMENT "fecha_inicio_jucio",
    updated_at DATETIME,
    accionante varchar(100) not null,
    accionado varchar(100) not null,
    numero_expediente int UNSIGNED not null,
    FOREIGN KEY (numero_expediente) REFERENCES expediente_local(numero_expediente),
    id_unidad_judicial INT UNSIGNED not null,
    FOREIGN KEY (id_unidad_judicial) REFERENCES unidad_judicial(id_unidad_judicial)
);

    create table juez(
        id_juez int UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        nombre_juez varchar(100),
        created_at DATETIME,
        updated_at DATETIME
    );

    create table juicio_juez(
        created_at DATETIME COMMENT "fecha en q este juex empieza el juicio",
        updated_at DATETIME COMMENT "fecha en q este juez termina o se retira del juicio",
        numero_juicio varchar(100) not null,
        FOREIGN KEY (numero_juicio) REFERENCES juicio(numero_juicio),
        id_juez int UNSIGNED not null,
        FOREIGN KEY (id_juez) REFERENCES juez(id_juez),
        PRIMARY KEY (numero_juicio, id_juez)
    );

    create table observaciones_juicio(
        id_observacion int UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        created_at DATETIME COMMENT "fecha de la observacion del juicio",
        updated_at DATETIME,
        descripcion text not null,
        numero_juicio varchar(100) not null,
        FOREIGN KEY (numero_juicio) REFERENCES juicio(numero_juicio)
    );

create table movimiento_juicio(
    id_movimiento int UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    detalle text not null,
    created_at DATETIME COMMENT "fecha del movimiento del juicio",
    updated_at DATETIME,
    numero_juicio varchar(100) not null,
    FOREIGN KEY (numero_juicio) REFERENCES juicio(numero_juicio)
);

    create table adjuntos_movimiento_juicio(
        id_archivo int UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        file_name text not null,
        file_name_hash text not null,
        extension varchar(10) not null,
        created_at DATETIME,
        updated_at DATETIME,
        id_movimiento int UNSIGNED not null,
        FOREIGN KEY (id_movimiento) REFERENCES movimiento_juicio(id_movimiento)
    );

create table audiencia(
    id_audiencia int UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    estado varchar(50) default "Pendiente" check(estado="Pendiente" or estado="Realizado" or estado="Postergado"),
    created_at DATETIME COMMENT "fecha asignada del juicio",
    updated_at DATETIME,
    observaciones_audiencia text,
    tipo varchar (10) not null check(tipo="Presencial" or tipo="Virtual"),
    numero_juicio varchar(100) not null,
    FOREIGN KEY (numero_juicio) REFERENCES juicio(numero_juicio)
);

    create table audiencia_presencial(
        direccion varchar (150) not null,
        id_audiencia int UNSIGNED NOT NULL,
        FOREIGN KEY (id_audiencia) REFERENCES audiencia(id_audiencia),
        primary key (id_audiencia),
        created_at DATETIME,
        updated_at DATETIME
    );

    create table audiencia_virtual(
        direccion_web varchar(150) not null,
        id_audiencia int UNSIGNED NOT NULL,
        FOREIGN KEY (id_audiencia) REFERENCES audiencia(id_audiencia),
        primary key (id_audiencia),
        created_at DATETIME,
        updated_at DATETIME
    );

create table usuarios(
    cedula varchar(10) not null primary key,
    rol VARCHAR(20) default "Admin" CHECK( rol="Admin" or rol="Asistente"),
    nombres VARCHAR(50) not null,
    apellidos varchar(50) not null,
    telefono varchar(10) not null,
    correo varchar(100) not null,
    user_name varchar(50),
    password text not null,
    created_at DATETIME,
    updated_at DATETIME
);

CREATE TABLE pass_master(
    password text not null,
    created_at DATETIME,
    updated_at DATETIME
);


INSERT INTO `pass_master` VALUES ('$2y$10$CdOK6vNq.XAEL4Sh5dvFIeL4QXHQ.XmTvQgcdcFi75.gl4IdiniDy','2022-07-08 07:17:26','2022-07-08 07:17:26');