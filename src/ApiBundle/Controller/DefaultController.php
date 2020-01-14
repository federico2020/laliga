<?php

namespace ApiBundle\Controller;

use ApiBundle\Entity\Club;
use ApiBundle\Entity\Jugador;
use ApiBundle\Entity\Perfil;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends AbstractFOSRestController
{

    /**
     * Obtener lista clubs
     */
    public function getInitDataAction(){

        $output = ['error'=>false,'msg'=>''];
        $em = $this->getDoctrine()->getManager();

        $query = $em->createQuery("
          SELECT c 
          FROM ApiBundle:Club c
        ");
        $clubs = $query->getArrayResult();

        $query = $em->createQuery("
          SELECT j.id, j.nombre, p.nombre perfil, c.nombre club, j.salario, j.posicion 
          FROM ApiBundle:Jugador j 
          JOIN ApiBundle:Club c WITH j.club = c.id
          JOIN ApiBundle:Perfil p WITH j.perfil = p.id
        ");
        $jugadores = $query->getArrayResult();

        $query = $em->createQuery("
          SELECT p 
          FROM ApiBundle:Perfil p
        ");
        $perfiles = $query->getArrayResult();

        return $this->json([
            'clubs'=>$clubs,
            'jugadores'=>$jugadores,
            'perfiles'=>$perfiles
        ]);
    }

    /**
     * Obtener lista clubs
    */
    public function getClubsAction(){

        $output = ['error'=>false,'msg'=>''];
        $em = $this->getDoctrine()->getManager();

        $query = $em->createQuery("
          SELECT c 
          FROM ApiBundle:Club c
        ");
        $clubs = $query->getArrayResult();

        return $this->json([
            'clubs'=>$clubs
        ]);
    }


    /**
     * Obtener club
     */
    public function getClubAction(int $id){

    }


    /**
     * Alta club
     */
    public function postClubAction(Request $request){

        $output = ['error'=>false,'msg'=>''];
        $em = $this->getDoctrine()->getManager();

        $nombre = strtoupper(trim($request->get('nombre')));
        $escudo = trim($request->get('escudo'));
        $ls = $request->get('limiteSalarial');

        if(empty($nombre) || strlen($nombre)<=3){
            $output['error']=true;
            $output['msg']='El nombre del club es obligatorio y debe contener como mínimo 3 caracteres';
        }
        elseif (empty($ls) || !is_numeric($ls)){
            $output['error']=true;
            $output['msg']='El límite salarial del club es obligatorio y debe ser un valor númerico';
        }

        $existe  = $em->getRepository(Club::class)->findOneBy(['nombre'=>$nombre]);

        if($existe){
            $output['error']=true;
            $output['msg']='El club ya existe';
        }

        if($output['error']===false){
            $club = new Club();
            $club->setNombre($nombre);
            $club->setEscudo($escudo);
            $club->setLimiteSalarial($ls);

            $em->persist($club);
            $em->flush();
        }

        return $this->json($output);
    }

    /**
     * Eliminar club
     */
    public function deleteClubAction(int $id){

        $output = ['error'=>false,'msg'=>''];
        $em = $this->getDoctrine()->getManager();

        $club  = $em->getRepository(Club::class)->find($id);

        if(empty($club)){
            $output['error']=true;
            $output['msg']='El club no existe';
        }
        else{

            $jugadores = $em->getRepository(Jugador::class)->findOneBy(['club'=>$club]);

            if($jugadores){
                $output['error']=true;
                $output['msg']='No se puede borrar porque existen jugadores en este club';
            }
            else{
                $em->remove($club);
                $em->flush();
            }
        }

        return $this->json($output);
    }

    /**
     * Modificar club
     */
    public function putClubAction(int $id){

    }

    /**
     * Obtener lista jugadores
     */
    public function getPlayersAction(Request $request){
        $output = ['error'=>false,'msg'=>''];
        $em = $this->getDoctrine()->getManager();

        $club = $output['club']= $request->get('club');
        $perfiles = $output['perfiles']= $request->get('perfil');


        $qb = $em->createQueryBuilder();
        $qb->select('j.id, j.nombre, p.nombre perfil, c.nombre club, j.salario, j.posicion, j.email, j.fechaNacimiento')
            ->from('ApiBundle:Jugador', 'j')
            ->join('j.club','c')
            ->join('j.perfil','p');

        if($club>0){
            $qb->where("c.id = $club");
        }

        if(count($perfiles)>0 && $perfiles[0]!=0){
            $qb->andWhere(
                $qb->expr()->in('p.id', $perfiles)
            );
        }

        $output['debug'] = $qb->getQuery()->getSql();
        $jugadores = $qb->getQuery()->getArrayResult();

        $output['jugadores']=$jugadores;
        return $this->json($output);
    }


    /**
     * Obtener jugador
     */
    public function getPlayerAction(int $id){
        $output = ['error'=>false,'msg'=>''];
        $em = $this->getDoctrine()->getManager();

        $query = $em->createQuery("
          SELECT j.id, j.nombre, p.id perfil, c.id club, j.salario, j.posicion, j.email, j.fechaNacimiento 
          FROM ApiBundle:Jugador j 
          JOIN ApiBundle:Club c WITH j.club = c.id
          JOIN ApiBundle:Perfil p WITH j.perfil = p.id
          WHERE j.id = $id
        ");
        $jugador = $query->getArrayResult();


        if(empty($jugador)){
            $output['error']=true;
            $output['msg']='El jugador no existe';
        }
        else{
            $output['jugador']=$jugador[0];
        }

        return $this->json($output);
    }


    /**
     * Alta jugador
     */
    public function postPlayerAction(Request $request){
        $em = $this->getDoctrine()->getManager();

        $nombre = strtoupper(trim($request->get('nombre')));
        $clubId = $request->get('club');
        $perfilId = $request->get('perfil');
        $posicion = $request->get('posicion');
        $fechaNacimiento = $request->get('fechaNacimiento');
        $salario = $request->get('salario');
        $email = trim($request->get('email'));

        $club  = $em->getRepository(Club::class)->find($clubId);
        $perfil  = $em->getRepository(Perfil::class)->find($perfilId);

        $output = $this->checkPlayer($request);

        if($output['error']===false){
            $jugador = new Jugador();
            $jugador->setNombre($nombre);
            $jugador->setEmail($email);
            $jugador->setFechaNacimiento(new \DateTime($fechaNacimiento));
            $jugador->setPosicion($posicion);
            $jugador->setFechaAlta(new \DateTime());
            $jugador->setClub($club);
            $jugador->setPerfil($perfil);
            $jugador->setSalario($salario);

            $em->persist($jugador);
            $em->flush();
        }

        return $this->json($output);
    }

    /**
     * Eliminar jugador
     */
    public function deletePlayerAction(int $id){

        $output = ['error'=>false,'msg'=>''];
        $em = $this->getDoctrine()->getManager();

        $jugador  = $em->getRepository(Jugador::class)->find($id);

        if(empty($jugador)){
            $output['error']=true;
            $output['msg']='El jugador no existe';
        }
        else{
            $em->remove($jugador);
            $em->flush();
        }

        return $this->json($output);
    }

    /**
     * Modificar jugador
     */
    public function putPlayerAction(Request $request){
        $em = $this->getDoctrine()->getManager();

        $id = $request->get('id');
        $nombre = strtoupper(trim($request->get('nombre')));
        $clubId = $request->get('club');
        $perfilId = $request->get('perfil');
        $posicion = $request->get('posicion');
        $fechaNacimiento = $request->get('fechaNacimiento');
        $salario = $request->get('salario');
        $email = trim($request->get('email'));

        $jugador  = $em->getRepository(Jugador::class)->find($id);

        if(empty($jugador)){
            return $this->json(['error'=>true,'msg'=>'Jugador no encontrado']);
        }

        $club  = $em->getRepository(Club::class)->find($clubId);
        $perfil  = $em->getRepository(Perfil::class)->find($perfilId);

        $output = $this->checkPlayer($request);

        if($output['error']===false){
            $jugador->setNombre($nombre);
            $jugador->setEmail($email);
            $jugador->setFechaNacimiento(new \DateTime($fechaNacimiento));
            $jugador->setPosicion($posicion);
            $jugador->setFechaAlta(new \DateTime());
            $jugador->setClub($club);
            $jugador->setPerfil($perfil);
            $jugador->setSalario($salario);

            $em->persist($jugador);
            $em->flush();
        }

        return $this->json($output);
    }


    private function checkPlayer($request){
        $output = ['error'=>false,'msg'=>''];
        $em = $this->getDoctrine()->getManager();

        $id = strtoupper(trim($request->get('id')));
        $nombre = strtoupper(trim($request->get('nombre')));
        $clubId = $request->get('club');
        $perfilId = $request->get('perfil');
        $fechaNacimiento = $request->get('fechaNacimiento');
        $salario = $request->get('salario');
        $email = trim($request->get('email'));

        $club  = $em->getRepository(Club::class)->find($clubId);
        $perfil  = $em->getRepository(Perfil::class)->find($perfilId);

        if(empty($nombre) || strlen($nombre)<=3){
            $output['error']=true;
            $output['msg']='El nombre del jugador es obligatorio y debe contener como mínimo 3 caracteres';
        }
        elseif(empty($club)){
            $output['error']=true;
            $output['msg']='El club no existe';
        }
        elseif(empty($salario) || !is_numeric($salario)){
            $output['error']=true;
            $output['msg']='El salario es obligatorio';
        }
        elseif(empty($email)){
            $output['error']=true;
            $output['msg']='El email es obligatorio';
        }
        else{
            $existe  = $em->getRepository(Jugador::class)->findOneBy(['club'=>$nombre]);

            if($existe){
                $output['error']=true;
                $output['msg']='El jugador ya existe';
            }
            else{
                //Controlamos máximo salarial y jugadores por club
                $jugadores  = $em->getRepository(Jugador::class)->findBy(['club'=>$club]);

                $maxSalarial = $club->getLimiteSalarial();
                $maxJugadores = 5;  //no canteranos
                $totalSalarios = $salario;
                $totalJugadores=!in_array(strtoupper($perfil->getNombre()),['CANTERANO','CANTERANOS','JUVENIL','JUVENILES'])?1:0;
                $existeEntrenador = false;
                if(count($jugadores)>0){
                    foreach ($jugadores as $jugador){
                        //Si estamos actualizando no se debe tener en cuenta el jugador actual
                        if($id && $id==$jugador->getId()) continue;

                        $totalSalarios+=$jugador->getSalario();

                        $perfilNombre = $jugador->getPerfil()->getNombre();
                        if(!in_array(strtoupper($perfilNombre),['CANTERANO','CANTERANOS','JUVENIL','JUVENILES'])){
                            $totalJugadores++;
                        }

                        if(strtoupper($perfilNombre)=='ENTRENADOR')$existeEntrenador=true;
                    }
                }


                if($existeEntrenador && strtoupper($perfil->getNombre())=='ENTRENADOR'){
                    $output['error']=true;
                    $output['msg']="Ya hay entrenador para este club";
                }
                elseif($totalSalarios>$maxSalarial){
                    $output['error']=true;
                    $output['msg']="Se supera el máximo salarial para el club ($totalSalarios > $maxSalarial)";
                }
                elseif($totalJugadores>$maxJugadores){
                    $output['error']=true;
                    $output['msg']='Se supera el máximo de jugadores no canteranos';
                }
                else{
                    // Cálculo Edad
                    $cumpleanos = new \DateTime($fechaNacimiento);
                    $hoy = new \DateTime();
                    $annos = $hoy->diff($cumpleanos);
                    $edad = $annos->y;

                    //Agrego un mínimo y máximo de edad para evitar errores
                    if(!is_numeric($edad) || $edad<6 || $edad>50){
                        $output['error']=true;
                        $output['msg']='Revise la edad';
                    }
                    elseif(in_array(strtoupper($perfil->getNombre()),['CANTERANO','CANTERANOS','JUVENIL','JUVENILES'])){
                        //Si es canterano o juvenil no puede tener mas de 23 años
                        if($edad>23){
                            $output['error']=true;
                            $output['msg']='Un canterano no puede tener mas de 23 años';
                        }
                    }
                }
            }
        }

        return $output;
    }

    /**
     * Alta perfil
     */
    public function postPerfilAction(Request $request){

        $output = ['error'=>false,'msg'=>''];
        $em = $this->getDoctrine()->getManager();

        $nombre = strtoupper(trim($request->get('nombre')));

        if(empty($nombre) || strlen($nombre)<=3){
            $output['error']=true;
            $output['msg']='El nombre del perfil es obligatorio y debe contener como mínimo 3 caracteres';
        }

        $existe  = $em->getRepository(Perfil::class)->findOneBy(['nombre'=>$nombre]);

        if($existe){
            $output['error']=true;
            $output['msg']='El perfil ya existe';
        }

        if($output['error']===false){
            $club = new Perfil();
            $club->setNombre($nombre);

            $em->persist($club);
            $em->flush();
        }

        return $this->json($output);
    }

    /**
     * Eliminar perfil
     */
    public function deletePerfilAction(int $id){

        $output = ['error'=>false,'msg'=>''];
        $em = $this->getDoctrine()->getManager();

        $perfil  = $em->getRepository(Perfil::class)->find($id);

        if(empty($perfil)){
            $output['error']=true;
            $output['msg']='El perfil no existe';
        }
        else{

            $jugadores = $em->getRepository(Jugador::class)->findOneBy(['perfil'=>$perfil]);

            if($jugadores){
                $output['error']=true;
                $output['msg']='No se puede borrar porque existen jugadores con este perfil';
            }
            else{
                $em->remove($perfil);
                $em->flush();
            }
        }

        return $this->json($output);
    }
}
