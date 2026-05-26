package sv.edu.udb.Repositorios;

import sv.edu.udb.Modelos.User;
import org.springframework.data.jpa.repository.JpaRepository;
import org.springframework.stereotype.Repository;
import java.util.Optional;

@Repository
public interface UserRepository extends JpaRepository<User, Integer> {
    // Necesario para el login: buscar usuario por nombre de usuario
    Optional<User> findByUsername(String username);

    // Verifica si el usuario existe durante el registro
    boolean existsByUsername(String username);
}
